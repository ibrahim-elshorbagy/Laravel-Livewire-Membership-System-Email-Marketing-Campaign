<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Site\ApiRequest;
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
use LucasDotVin\Soulbscription\Models\Feature;
use Exception;


class EmailGatewayController extends Controller
{
    protected $apiPassword = '6Sb8E3cGG2bS1a';
    protected $batchSize = 4;
    protected $allowedUserAgents = [
        'Google-Apps-Script',
    ];
    protected $emailSendingFeatureName;
    protected $emailSendingFeatureTotalQouta;

    public function __construct()
    {
        $feature = Feature::find(2);
        $this->emailSendingFeatureName = $feature->name;
    }
    // Check user agent ----------------------------------------------------------------------------
    protected function checkUserAgent(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        foreach ($this->allowedUserAgents as $allowed) {
            if (strpos($userAgent, $allowed) != false) {
                return true;
            }
        }

        return false;
    }

    public function getDetails(Request $request)
    {
            $startTime = microtime(true);
            // Validate request data ----------------------------------------------------------------------------
            // I tried my best to secure it This Won't work with normal user Just some one trying to hack or somthing
            $validator = Validator::make($request->all(), [
                'serverid' => 'string',
            ], [
                'serverid.required' => 'Server ID is required',
                'serverid.string' => 'Server ID must be a string',
            ]);
            if ($validator->fails()) {
                try{
                    return response()->json([
                        'error' => 'Validation failed',
                        'message' => implode(', ', $validator->errors()->all()),
                        'error_number'=> 1,
                        'server' => [
                            'id' => $request->serverid
                        ]
                    ], 422);

                }finally {
                    $executionTime = microtime(true) - $startTime;
                    ApiRequest::create([
                        'serverid' => $request->serverid,
                        'execution_time' => $executionTime,
                        'status' => 'failed',
                        'error_data' => [
                            'error' => 'Validation failed',
                            'message' => implode(', ', $validator->errors()->all()),
                            'error_number' => 1
                        ]
                    ]);
                }

            }



            // Validate request data ----------------------------------------------------------------------------
            // I tried my best to secure it
            $validator = Validator::make($request->all(), [
                'serverid' => 'required|string|exists:servers,name',
                'pass' => 'required|string',
                'quota' => 'required|integer'
            ], [
                'serverid.required' => 'Server ID is required',
                'serverid.string' => 'Server ID must be a string',
                'serverid.exists' => 'Invalid server ID',
                'pass.required' => 'Password is required',
                'quota.required' => 'Quota is required'
            ]);



            if ($validator->fails()) {

                try{

                    return response()->json([
                        'error' => 'Validation failed',
                        'message' => implode(', ', $validator->errors()->all()),
                        'error_number'=> 1,
                        'server' => [
                            'id' => $request->serverid
                        ]
                    ], 422);

                }finally {
                    $executionTime = microtime(true) - $startTime;
                    ApiRequest::create([
                        'serverid' => $request->serverid,
                        'execution_time' => $executionTime,
                        'status' => 'failed',
                        'error_data' => [
                            'error' => 'Validation failed',
                            'message' => implode(', ', $validator->errors()->all()),
                            'error_number' => 1
                        ]
                    ]);
                }

            }


            $validatedData = $validator->validated();
            $serverid      = $validatedData['serverid'];
            $pass          = $validatedData['pass'];
            $quota         = $validatedData['quota'];


            // Check for our_devices ----------------------------------------------------------------------------

            $our_devices =  SiteSetting::getValue('our_devices');

            if($our_devices){

                if (!$this->checkUserAgent($request)) {

                    try{

                        return response()->json([
                            'error' => 'Access Denied',
                            'message' => 'Invalid User-Agent',
                            'error_number'=> 2,
                            'user_agent' => $request->header('User-Agent'),
                            'server' => [
                                'id' => $serverid
                            ]
                        ], 403);

                    }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                                'error' => 'Access Denied',
                                'message' => 'Invalid User-Agent',
                                'error_number' => 2
                            ]
                        ]);
                    }

                }
            }







            // Check for maintenance mode ----------------------------------------------------------------------------
            $maintenance =  SiteSetting::getValue('maintenance');
            if ($maintenance) {

                try{


                    return response()->json([
                        'error' => 'Maintenance Mode',
                        'message' => 'System is currently under maintenance',
                        'error_number'=> 3,
                        'server' => [
                            'id' => $serverid
                        ]
                    ], 503);

                }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                            'error' => 'Maintenance Mode',
                            'message' => 'System is currently under maintenance',
                            'error_number' => 3
                        ]
                        ]);
                }



            }



















            // Check API pass ----------------------------------------------------------------------------
            if ($request->pass != $this->apiPassword) {

                try{

                        return response()->json([
                            'error' => 'Authentication failed',
                            'message' => 'Invalid API credentials',
                            'error_number'=> 4,
                            'server' => [
                                'id' => $request->serverid
                            ]
                        ], 401);

                    }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                                'error' => 'Authentication failed',
                                'message' => 'Invalid API credentials',
                                'error_number' => 4
                            ]
                        ]);
                }

            }











            // Start Server && User check ----------------------------------------------------------------------------

            // Validate server assignment
            $server = Server::where('name', $request->serverid)->first();
            $this->batchSize = $server->emails_count; //Update batchSize

            $user = User::where('id', $server->assigned_to_user_id)->first();

            if (!$user) {
                try {
                    return response()->json([
                        'error' => 'Invalid user',
                        'message' => 'No Assigned user found',
                        'error_number' => 10,
                        'server' => [
                            'id' => $request->serverid
                        ]
                    ], 404);
                } finally {
                    $executionTime = microtime(true) - $startTime;
                    ApiRequest::create([
                        'serverid' => $serverid,
                        'execution_time' => $executionTime,
                        'status' => 'failed',
                        'error_data' => [
                            'error' => 'Invalid user',
                            'message' => 'No Assigned user found',
                            'error_number' => 10
                        ]
                    ]);
                }
            }

            if (!$user->active) {

                try{

                            return response()->json([
                                'error' => 'Account inactive',
                                'message' => 'User account is currently inactive',
                                'error_number'=> 5,
                                'server' => [
                                    'id' => $request->serverid
                                ]
                            ], 403);

                    }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                                    'error' => 'Account inactive',
                                    'message' => 'User account is currently inactive',
                                    'error_number' => 5
                                ]
                        ]);
                }


            }










            // Validate subscription ----------------------------------------------------------------------------
            $subscription = $user->subscription;
            if (!$subscription) {

                try{

                        return response()->json([
                            'error' => 'No subscription',
                            'message' => 'Active subscription required',
                            'error_number'=> 6,
                            'server' => [
                                'id' => $request->serverid
                            ]
                        ], 403);

                    }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                                'error' => 'No subscription',
                                'message' => 'Active subscription required',
                                'error_number' => 6
                            ]
                        ]);
                }

            }










            // Check if can consume batch size ----------------------------------------------------------------------------
            if (!$user->canConsume($this->emailSendingFeatureName, $this->batchSize)) {

                try{

                        return response()->json([
                            'error' => 'Quota exceeded',
                            'message' => 'Email sending limit reached',
                            'error_number'=> 7,
                            'user' => [
                                'id' => $user->id,
                                'name' => $user->first_name . ' ' . $user->last_name,
                                'email' => $user->email,
                                'EmailSendingRemainingQouta' => $user->balance($this->emailSendingFeatureName),
                            ],
                            'server' => [
                                'id' => $request->serverid
                            ]
                        ], 403);

                    }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                                    'error' => 'Quota exceeded',
                                    'message' => 'Email sending limit reached',
                                    'error_number' => 7
                                ]
                        ]);
                }

            }










            // Get active campaign ----------------------------------------------------------------------------
            $campaign = Campaign::whereHas('servers', function($query) use ($server) {
                $query->where('server_id', $server->id);
            })
            ->where('status', 'Sending')
            ->with(['message', 'emailLists'])
            ->first();

            if (!$campaign) {

                try{

                        return response()->json([
                            'error' => 'No active campaign',
                            'message' => 'No active campaign found for this server',
                            'error_number'=> 8,
                            'user' => [
                                'id' => $user->id,
                                'name' => $user->first_name . ' ' . $user->last_name,
                                'email' => $user->email,
                                'EmailSendingRemainingQouta' => $user->balance($this->emailSendingFeatureName),
                            ],
                            'server' => [
                                'id' => $server->id,
                                'name' => $server->name,
                                'PreSendServerQuota' => $server->current_quota,

                            ]
                        ], 404);

                    }finally {

                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                                'error' => 'No active campaign',
                                'message' => 'No active campaign found for this server',
                                'error_number' => 8
                            ]
                        ]);
                }

            }










            // Start To Update && unsubscribe information ----------------------------------------------------------------------------

            $server->update([
                'current_quota' => $request->quota,
                'last_access_time'=> Carbon::now(),
            ]);

            // Get user's unsubscribe information
            $userInfo = UserInfo::where('user_id', $user->id)->first();
            $unsubscribeData = [];
            if ($userInfo && $userInfo->unsubscribe_status) {
                $unsubscribeHtml = '<hr><p style="text-align: center;">';
                $unsubscribeHtml .= $userInfo->unsubscribe_pre_text . ' ';

                // Check if the unsubscribe_link is an email or URL
                if (filter_var($userInfo->unsubscribe_link, FILTER_VALIDATE_EMAIL)) {
                    // It's an email address
                    $unsubscribeHtml .= '<a href="mailto:' . $userInfo->unsubscribe_link . '">' . $userInfo->unsubscribe_text . '</a>.';
                } else {
                    // It's a URL
                    $unsubscribeHtml .= '<a href="' . $userInfo->unsubscribe_link . '">' . $userInfo->unsubscribe_text . '</a>.';
                }

                $unsubscribeHtml .= '</p>';

                $unsubscribeData = [
                    'unsubscribe_html' => $unsubscribeHtml,
                ];
            }







            // Process emails ----------------------------------------------------------------------------

            $result = $this->processEmails($campaign, $user);
            $emailsToSend = $result['emails'];
            $summary = $result['summary'];

            if (empty($emailsToSend)) {

                try{

                    return response()->json([
                        'error' => 'No Emails avaiable',
                        'message' => "No emails found for this server's campaign ",
                        'error_number'=> 9,
                        'referer' => $request->server('HTTP_REFERER'),
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'email' => $user->email,
                            'EmailSendingRemainingQouta' => $user->balance($this->emailSendingFeatureName),
                        ],
                        'server' => [
                            'id' => $server->id,
                            'name' => $server->name,
                            'PreSendServerQuota' => $server->current_quota,
                            ],
                    ], 404);

                    }finally {
                        $executionTime = microtime(true) - $startTime;
                        ApiRequest::create([
                            'serverid' => $serverid,
                            'execution_time' => $executionTime,
                            'status' => 'failed',
                            'error_data' => [
                            'error' => 'No Emails avaiable',
                            'message' =>  "No emails found for this server's campaign ",
                            'error_number' => 9
                        ]
                        ]);
                    }

            }

            // Return success response with unsubscribe data if available ----------------------------------------------------------------------------
            $response = [
                'status' => 'success',
                'message' => 'Batch retrieved successfully',
                'referer' => $request->server('HTTP_REFERER'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'EmailSendingRemainingQouta' => $user->balance($this->emailSendingFeatureName),
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

            // Success Response ----------------------------------------------------------------------------

            try{
                return response()->json($response);

                }finally {
                    $executionTime = microtime(true) - $startTime;
                    ApiRequest::create([
                        'serverid' => $serverid,
                        'execution_time' => $executionTime,
                        'status' => 'success'
                    ]);
            }



    }

    protected function processEmails($campaign, $user)
    {
        $emailsToSend = [];

        // Get campaign totals excluding hard bounces
        $totalCampaignEmails = EmailList::whereIn('list_id', $campaign->emailLists->pluck('id'))
            ->where('is_hard_bounce', false)
            ->count();

        $totalHardBounces = EmailList::whereIn('list_id', $campaign->emailLists->pluck('id'))
            ->where('is_hard_bounce', true)
            ->count();

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
                ->where('is_hard_bounce', false)
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
                        'name' => $email->name,
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

        $emailSendingFeature = $user->subscription->plan->features->firstWhere('name', $this->emailSendingFeatureName);


        // Retrieve the total quota (limit) from the feature's pivot table
        $emailSendingFeatureTotalQuota = $emailSendingFeature->pivot->charges;

        // Retrieve the User's feature Remaining balance
        $emailSendingUserBalance = $user->balance($this->emailSendingFeatureName);

        // Calculate the amount the user has consumed (total quota - remaining balance)
        $oldConsumed = $emailSendingFeatureTotalQuota - $emailSendingUserBalance;

        // Add the amount the user is about to consume (count of emails to send)
        $newTotalConsumed = $oldConsumed + count($emailsToSend);

        // Set the new consumed quota
        $user->setConsumedQuota($this->emailSendingFeatureName, $newTotalConsumed);


            $newProcessedEmails = $processingSummary['processed_emails'] + count($emailsToSend);
            $processingSummary['processed_emails'] = min($newProcessedEmails, $totalCampaignEmails);
            $processingSummary['remaining_emails'] = max(0, $totalCampaignEmails - $newProcessedEmails);
            $processingSummary['completion_percentage'] = min(100,
                round(($processingSummary['processed_emails'] / $totalCampaignEmails) * 100, 2)
            );

            if ($processingSummary['remaining_emails'] === 0 || $processingSummary['remaining_emails'] === $totalHardBounces) {
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
