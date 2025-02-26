<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\EmailHistory;
use App\Models\EmailList;
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

            // if (!$this->checkUserAgent($request)) {
            //     return response()->json([
            //         'error' => 'Access Denied',
            //         'message' => 'Invalid User-Agent',
            //         'user_agent' => $request->header('User-Agent'),
            //         'server' => [
            //             'id' => $request->serverid ?? null
            //         ]
            //     ], 403);
            // }



            // Validate request
            $validator = Validator::make($request->all(), [
                'serverid' => 'required|exists:servers,name',
                'username' => 'required|string|exists:users,username',
                'pass' => 'required|string',
                'quota'=>'required'
            ], [
                'serverid.required' => 'Server ID is required',
                'serverid.exists' => 'Invalid server ID',
                'username.required' => 'Username is required',
                'username.exists' => 'Invalid username',
                'pass.required' => 'Password is required',
                'quota.required' => 'Quota is required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => implode(', ', $validator->errors()->all()),
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
            //     'username' => $request->username,
            //     'serverid' => $request->serverid
            // ]);

            // Check API pass
            if ($request->pass !== $this->apiPassword) {
                return response()->json([
                    'error' => 'Authentication failed',
                    'message' => 'Invalid API credentials',
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 401);
            }

            // Find and validate user
            $user = User::where('username', $request->username)->first();

            if (!$user->active) {
                return response()->json([
                    'error' => 'Account inactive',
                    'message' => 'User account is currently inactive',
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 403);
            }

            // Validate subscription
            $subscription = $user->lastSubscription();
            if (!$subscription) {
                return response()->json([
                    'error' => 'No subscription',
                    'message' => 'Active subscription required',
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 403);
            }

            // Check if can consume batch size
            if (!$user->canConsume('Email Sending', $this->batchSize)) {
                return response()->json([
                    'error' => 'Quota exceeded',
                    'message' => 'Email sending limit reached',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'remaining_quota' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 403);
            }

            // Validate server assignment
            $server = $user->servers()
                ->where('name', $request->serverid)
                ->first();

            if (!$server) {
                return response()->json([
                    'error' => 'Server not found',
                    'message' => 'Server not assigned to user',
                    'server' => [
                        'id' => $request->serverid
                    ]
                ], 404);
            }

            $server->update([
                'current_quota' => $request->quota,
                'last_access_time'=> Carbon::now(),
            ]);
            // Get active campaign
            $campaign = Campaign::whereHas('servers', function($query) use ($server) {
                $query->where('server_id', $server->id);
            })
            ->where('status', 'Sending')
            ->with(['message', 'emailLists'])
            ->first();

            if (!$campaign) {
                return response()->json([
                    'error' => 'No active campaign',
                    'message' => 'No active campaign found for this server',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'remaining_quota' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'quota' => $server->current_quota,

                    ]
                ], 404);
            }

            // Check if campaign has lists
            if ($campaign->emailLists->isEmpty()) {
                return response()->json([
                    'error' => 'Invalid campaign configuration',
                    'message' => 'Campaign has no email lists attached',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'remaining_quota' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'quota' => $server->current_quota,

                    ],
                    'campaign' => [
                        'id' => $campaign->id,
                        'title' => $campaign->title
                    ]
                ], 400);
            }




            // Process emails
            try {
                $result = $this->processEmails($campaign, $user);
                $emailsToSend = $result['emails'];
                $summary = $result['summary'];

                if (empty($emailsToSend)) {
                    if ($summary['remaining_emails'] === 0) {
                        return response()->json([
                            'status' => 'completed',
                            'message' => 'Campaign completed - all emails processed',
                            'referer' => $request->server('HTTP_REFERER'),
                            'user' => [
                                'id' => $user->id,
                                'name' => $user->first_name . ' ' . $user->last_name,
                                'email' => $user->email,
                                'remaining_quota' => $user->balance('Email Sending'),
                            ],
                            'server' => [
                                'id' => $server->id,
                                'name' => $server->name,
                                'quota' => $server->current_quota,
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

                            ]
                        ], 200);
                    }

                    return response()->json([
                        'status' => 'no_emails',
                        'message' => 'No emails available for current batch',
                        'referer' => $request->server('HTTP_REFERER'),
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'email' => $user->email,
                            'remaining_quota' => $user->balance('Email Sending'),
                        ],
                        'server' => [
                            'id' => $server->id,
                            'name' => $server->name,
                            'quota' => $server->current_quota,

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

                        ]
                    ], 200);
                }

                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => 'Batch retrieved successfully',
                    'referer' => $request->server('HTTP_REFERER'),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'remaining_quota' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'quota' => $server->current_quota,

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
                ]);

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
                        'remaining_quota' => $user->balance('Email Sending'),
                    ],
                    'server' => [
                        'id' => $server->id,
                        'name' => $server->name,
                        'quota' => $server->current_quota,

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
        $totalProcessedEmails = EmailHistory::where('campaign_id', $campaign->id)
            ->whereIn('email_id', function($query) use ($campaign) {
                $query->select('id')
                    ->from('email_lists')
                    ->whereIn('list_id', $campaign->emailLists->pluck('id'));
            })
            ->count();

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
