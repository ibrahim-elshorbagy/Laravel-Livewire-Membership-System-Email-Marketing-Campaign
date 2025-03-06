<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Site\ApiError;
use App\Models\Admin\Site\SiteSetting;
use App\Models\User;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\EmailHistory;
use App\Models\EmailList;
use App\Models\Server;
use App\Models\UserInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class EmailGatewayController extends Controller
{
    protected $apiPassword = '6Sb8E3cGG2bS1a';
    protected $batchSize = 4;
    protected $allowedUserAgents = [
        'Google-Apps-Script',
    ];

    protected function checkUserAgent(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        foreach ($this->allowedUserAgents as $allowed) {
            if (strpos($userAgent, $allowed) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getDetails(Request $request)
    {
        try {

            $our_devices = SiteSetting::getValue('our_devices');
            if($our_devices){

                if (!$this->checkUserAgent($request)) {
                    $apiError = ApiError::create([
                        'serverid' => $request->serverid ?? null,
                        'error_data' => [
                            'error' => 'Access Denied',
                            'message' => 'Invalid User-Agent',
                            'error_number' => 1
                        ]
                    ]);
                    return response()->json([
                        'error' => 'Access Denied',
                        'message' => 'Invalid User-Agent',
                        'error_number'=> 1,
                        'user_agent' => $request->header('User-Agent'),
                        'server' => [
                            'id' => $request->serverid ?? null
                        ]
                    ], 403);
                }
            }

            $maintenance = SiteSetting::getValue('maintenance');

            if ($maintenance) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid ?? null,
                    'error_data' => [
                        'error' => 'Maintenance Mode',
                        'message' => 'System is currently under maintenance',
                        'error_number' => 2
                    ]
                ]);

                return response()->json([
                    'error' => 'Maintenance Mode',
                    'message' => 'System is currently under maintenance',
                    'error_number'=> 2,
                    'server' => [
                        'id' => $request->serverid ?? null
                    ]
                ], 503);
            }


            // Validate request
            $validator = Validator::make($request->all(), [
                'serverid' => 'required|exists:servers,name',
                'pass' => 'required|string',
                'quota'=>'required|integer'
            ], [
                'serverid.required' => 'Server ID is required',
                'serverid.exists' => 'Invalid server ID',
                'pass.required' => 'Password is required',
                'quota.required' => 'Quota is required'
            ]);

            if ($validator->fails()) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid ?? null,
                    'error_data' => [
                        'error' => 'Validation failed',
                        'message' => implode(', ', $validator->errors()->all()),
                        'error_number' => 3
                    ]
                ]);

                return response()->json([
                    'error' => 'Validation failed',
                    'message' => implode(', ', $validator->errors()->all()),
                    'error_number'=> 3,
                    'server' => [
                        'id' => $request->serverid ?? null
                    ]
                ], 422);
            }

            // Log::info('API Access Attempt', [
            //     'timestamp' => now()->format('Y-m-d H:i:s'),
            //     'ip' => $request->ip(),
            //     'origin' => $request->header('Origin'),
            //     'user_agent' => $request->header('User-Agent'),
            //     'serverid' => $request->serverid
            // ]);

            // Check API pass
            if ($request->pass !== $this->apiPassword) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid,
                    'error_data' => [
                        'error' => 'Authentication failed',
                        'message' => 'Invalid API credentials',
                        'error_number' => 4
                    ]
                ]);

                return response()->json([
                    'error' => 'Authentication failed',
                    'message' => 'Invalid API credentials',
                    'error_number'=> 4,
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 401);
            }



            // Validate server assignment
            $server = Server::where('name', $request->serverid)->first();

            // Find and validate user
            $user = User::where('id', $server->assigned_to_user_id)->first();

            if (!$user->active) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid,
                    'error_data' => [
                        'error' => 'Account inactive',
                        'message' => 'User account is currently inactive',
                        'error_number' => 5
                    ]
                ]);

                return response()->json([
                    'error' => 'Account inactive',
                    'message' => 'User account is currently inactive',
                    'error_number'=> 5,
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 403);
            }

            // Validate subscription
            $subscription = $user->lastSubscription();
            if (!$subscription) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid,
                    'error_data' => [
                        'error' => 'No subscription',
                        'message' => 'Active subscription required',
                        'error_number' => 6
                    ]
                ]);

                return response()->json([
                    'error' => 'No subscription',
                    'message' => 'Active subscription required',
                    'error_number'=> 6,
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 403);
            }

            // Check if can consume batch size
            if (!$user->canConsume('Email Sending', $this->batchSize)) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid,
                    'error_data' => [
                        'error' => 'Quota exceeded',
                        'message' => 'Email sending limit reached',
                        'error_number' => 7
                    ]
                ]);

                return response()->json([
                    'error' => 'Quota exceeded',
                    'message' => 'Email sending limit reached',
                    'error_number'=> 7,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'EmailSendingRemainingQouta' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 403);
            }


            // Get active campaign
            $campaign = Campaign::whereHas('servers', function($query) use ($server) {
                $query->where('server_id', $server->id);
            })
            ->where('status', 'Sending')
            ->with(['message', 'emailLists'])
            ->first();

            if (!$campaign) {
                $apiError = ApiError::create([
                    'serverid' => $request->serverid,
                    'error_data' => [
                        'error' => 'No active campaign',
                        'message' => 'No active campaign found for this server',
                        'error_number' => 8
                    ]
                ]);

                return response()->json([
                    'error' => 'No active campaign',
                    'message' => 'No active campaign found for this server',
                    'error_number'=> 8,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'EmailSendingRemainingQouta' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'PreSendServerQuota' => $server->current_quota,

                    ]
                ], 404);
            }

            $server->update([
                'current_quota' => $request->quota,
                'last_access_time'=> Carbon::now(),
            ]);

            // Get user's unsubscribe information
            $userInfo = UserInfo::where('user_id', $user->id)->first();
            $unsubscribeData = [];
            if ($userInfo && $userInfo->unsubscribe_status) {
                $unsubscribeData = [
                    'unsubscribe_email' => $userInfo->unsubscribe_email,
                    'unsubscribe_link' => $userInfo->unsubscribe_link
                ];
            }

            // Process emails
            try {
                $result = $this->processEmails($campaign, $user);
                $emailsToSend = $result['emails'];
                $summary = $result['summary'];

                if (empty($emailsToSend)) {

                $apiError = ApiError::create([
                    'serverid' => $request->serverid,
                    'error_data' => [
                        'error' => 'No Emails avaiable',
                        'message' =>  "No emails found for this server's campaign ",
                        'error_number' => 9
                    ]
                ]);

                    return response()->json([
                        'error' => 'No Emails avaiable',
                        'message' => "No emails found for this server's campaign ",
                        'error_number'=> 9,
                        'referer' => $request->server('HTTP_REFERER'),
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'email' => $user->email,
                            'EmailSendingRemainingQouta' => $user->balance('Email Sending'),
                        ],
                        'server' => [
                            'id' => $server->id,
                            'name' => $server->name,
                            'PreSendServerQuota' => $server->current_quota,
                            ],
                    ], 404);
                }

                // Return success response with unsubscribe data if available
                $response = [
                    'status' => 'success',
                    'message' => 'Batch retrieved successfully',
                    'referer' => $request->server('HTTP_REFERER'),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'EmailSendingRemainingQouta' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'PreSendServerQuota' => $server->current_quota,
                    ],
                    'campaign' => [
                        'id' => $campaign->id,
                        'title' => $campaign->title,
                        'message' => [
                            'subject' => $campaign->message->email_subject,
                            'html_content' => html_entity_decode($campaign->message->message_html),
                            'plain_text' => $campaign->message->message_plain_text,
                            'sender_name' => $campaign->message->sender_name,
                            'reply_to' => $campaign->message->reply_to_email,
                        ],
                        'processing_summary' => $summary,
                    ],
                    'emails' => $emailsToSend,
                ];

                // Add unsubscribe data if available
                if (!empty($unsubscribeData)) {
                    $response['unsubscribe'] = $unsubscribeData;
                }

                return response()->json($response);

            } catch (Exception $e) {
                Log::error('Email processing failed', [
                    'error' => $e->getMessage(),
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id
                ]);

                return response()->json([
                    'error' => 'Processing error',
                    'message' => $e->getMessage(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'EmailSendingRemainingQouta' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'PreSendServerQuota' => $server->current_quota,

                    ],
                    'campaign' => [
                        'id' => $campaign->id,
                        'title' => $campaign->title,
                        'processing_summary' => $summary ?? null
                    ]
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('API request failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'System error',
                'message' => $e->getMessage(),
                'server' => [
                    'id' => $request->serverid ?? null
                ]
            ], 500);
        }
    }

    protected function processEmails($campaign, $user)
    {
        $emailsToSend = [];

        // Get campaign totals - More accurate counting
        $totalCampaignEmails = EmailList::whereIn('list_id', $campaign->emailLists->pluck('id'))->count();

        // Only count valid email histories
        $totalProcessedEmails = EmailHistory::where('campaign_id', $campaign->id)->count();

        // Ensure we don't have negative numbers
        $remainingEmails = max(0, $totalCampaignEmails - $totalProcessedEmails);

        $processingSummary = [
            'total_emails' => $totalCampaignEmails,
            'processed_emails' => min($totalProcessedEmails, $totalCampaignEmails), // Don't exceed total
            'remaining_emails' => $remainingEmails,
            'completion_percentage' => $totalCampaignEmails > 0
                ? min(100, round(($totalProcessedEmails / $totalCampaignEmails) * 100, 2))
                : 0
        ];

        foreach ($campaign->emailLists as $list) {

            if (count($emailsToSend) >= $this->batchSize) break;

            $remainingInBatch = $this->batchSize - count($emailsToSend);

            $unsentEmails = EmailList::where('list_id', $list->id)
                ->whereNotExists(function($query) use ($campaign) {
                    $query->select(DB::raw(1))
                        ->from('email_histories')
                        ->whereColumn('email_histories.email_id', 'email_lists.id')
                        ->where('email_histories.campaign_id', $campaign->id);
                })
                ->limit($remainingInBatch)
                ->get();

            foreach ($unsentEmails as $email) {
                if (count($emailsToSend) >= $this->batchSize) break;

                DB::beginTransaction();
                try {
                    EmailHistory::create([
                        'email_id' => $email->id,
                        'campaign_id' => $campaign->id,
                        'status' => 'sent',
                        'sent_time' => Carbon::now(),
                    ]);

                    $emailsToSend[] = [
                        'id' => $email->id,
                        'email' => $email->email,
                        'sent_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("Failed to process email", [
                        'email_id' => $email->id,
                        'list_id' => $list->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new Exception("Failed to process email ID: {$email->id} in list: {$list->id}");
                }
            }
        }

        // Update summary with new batch - with safeguards
        if (count($emailsToSend) > 0) {
            $user->consume('Email Sending', (float) count($emailsToSend));

            $newProcessedEmails = $processingSummary['processed_emails'] + count($emailsToSend);
            $processingSummary['processed_emails'] = min($newProcessedEmails, $totalCampaignEmails);
            $processingSummary['remaining_emails'] = max(0, $totalCampaignEmails - $newProcessedEmails);
            $processingSummary['completion_percentage'] = min(100,
                round(($processingSummary['processed_emails'] / $totalCampaignEmails) * 100, 2)
            );

            if ($processingSummary['remaining_emails'] === 0) {
                DB::transaction(function() use ($campaign) {
                    // Update campaign status
                    $campaign->update(['status' => 'Completed']);

                    // Detach all servers from the campaign
                    $campaign->servers()->detach();
                });
            }
        }

        return [
            'emails' => $emailsToSend,
            'summary' => $processingSummary
        ];
    }
}
