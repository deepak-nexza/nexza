<?php

namespace App\Http\Controllers\Contracts\Traits;

use Auth;
use Event;
use Session;
use Request;
use Zendesk;
use Exception;
use Zendesk\API\Exceptions\ApiResponseException;

trait ZendeskTraits
{

    /**
     * Create Zendesk Ticket.
     *
     * @param object $userRepo
     * @param \Illuminate\Http\Request $request
     * @return array $ticket_response
     */
    public function createZendUser($userRepo, $request)
    {
        $request_array = [];
        try {
            $user_id = (int) $request->request->get('user_id');
            $user_info = $userRepo->getUserDetail($user_id);
            $zend_user_detail = $this->checkZendUser($userRepo, $user_info->email);

            if (!isset($zend_user_detail->error)) {
                if (isset($zend_user_detail->users[0])) {
                    $zend_user_id = $zend_user_detail->users[0]->id;
                    $user_detail = $zend_user_detail;
                } else {
                    $request_array = [
                        'external_id' => $user_id,
                        'email' => $user_info->email,
                        'name' => $user_info->first_name . ' ' . $user_info->last_name,
                        'verified' => true
                    ];

                    $user_detail = Zendesk::users()->createOrUpdate($request_array);
                    if ($user_detail) {
                        $zend_user_id = $user_detail->user->id;
                    } else {
                        $zend_user_id = null;
                    }

                    Event::fire(
                        "zendesk.createuser",
                        serialize(['user_id' => $user_id, 'by_whom_id' => $user_id, 'email' => $user_info->email, 'username' => $user_info->username])
                    );
                }

                $this->saveTicketLogInfo($userRepo, json_encode($request_array), $user_detail, 'user_create', null, null);
            } else {
                Session::flash('message', trans('messages.ticket_not_saved'));
                return redirect()->back();
            }


            return $zend_user_id;
        } catch (Exception $ex) {
            $user_detail = (object) array("error" => $ex->getMessage());
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $user_detail, 'user_create', null, null);
            return $user_detail;
        }
    }

    /**
     * Create Zendesk Ticket.
     *
     * @param \Illuminate\Http\Request $request
     * @return array $ticket_response
     */
    public function createZendTicket($userRepo, $request, $zend_user_id, $user_info_array)
    {
        $request_array = [];
        try {
            $question = $request->request->get('your_question');
            $visible_seperetor = '----------------------------------------';
            if ($user_info_array['user_type'] == 0) {
                $app_info = '';
                if (!empty($user_info_array['lead_no'])) {
                    $app_info = 'Lead ID : ' . $user_info_array['lead_no'];
                }
                $additional = 'User Name : ' . $user_info_array['username'] . "\n" . $app_info;
            } else {
                $additional = 'User Name : ' . $user_info_array['username'];
            }

            $user_id = (int) $request->request->get('user_id');
            $request_array = [
                'requester_id' => $zend_user_id, 'external_id' => $user_id,
                'ticket_form_id' => config('zendesk-laravel.ticket_form_id'),
                'subject' => 'Oriental Bank:Support Enquiry From '.ucfirst($user_info_array['first_name']).' '.ucfirst($user_info_array['last_name']),
                'comment' => ['body' => $question . "\n\n" . $visible_seperetor . "\n" . $additional], 'priority' => 'normal', 'brand_id' => config('zendesk-laravel.ob_brand_id')
            ];

            $ticket['response'] = Zendesk::tickets()->create($request_array);
            $ticket['request'] = $request_array;
            $ticket_response = $ticket['response'];
            return $ticket;
        } catch (Exception $ex) {
            $ticket_response = (object) array("error" => $ex->getMessage(), "request" => $request_array);
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $ticket_response, 'create', $tid = null, null);
            return $ticket_response;
        }
    }

    /**
     * Get ZenDesk ticket by ID.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param int $zend_ticket_id
     * @return object $ticket_detail
     */
    public function getZendTicketById($userRepo, $zend_ticket_id, $system_ticket_id)
    {
        try {
            $request_array = ['zend_ticket_id' => $zend_ticket_id];
            return $ticket_detail = Zendesk::tickets()->find($zend_ticket_id);
        } catch (Exception $ex) {
            $ticket_detail = (object) array("error" => $ex->getMessage());
            return $ticket_detail;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $ticket_detail, 'single_view', $system_ticket_id, null);
        }
    }

    /**
     * Get ZenDesk comments by ticket ID.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param int $zend_ticket_id
     * @return object $ticket_comments
     */
    public function getZendTicketCommentsById($userRepo, $zend_ticket_id, $system_ticket_id)
    {
        try {
            $request_array = ['zend_ticket_id' => $zend_ticket_id];
            return $ticket_comments = Zendesk::tickets($zend_ticket_id)->comments()->findAll();
        } catch (Exception $ex) {
            $ticket_comments = (object) array("error" => $ex->getMessage());
            return $ticket_comments;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $ticket_comments, 'get_comment', $system_ticket_id, null);
        }
    }

    /**
     * Create ZenDesk Comments.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param \Illuminate\Http\Request $request
     * @param array $system_ticket
     * @return object $comment_response
     */
    public function createZendTicketComment($userRepo, $request, $system_ticket)
    {
        $request_array = [];
        try {
            $zend_user_id = $system_ticket->zendesk_lead_id;
            $zend_ticket_id = (int) $request->request->get('ticket_id');
            $request_array = [
                'comment' => ['body' => $request->request->get('your_comment'), 'author_id' => $zend_user_id],
                'submitter_id' => $zend_user_id,
                'requester_id' => $zend_user_id
            ];
            $comment_response = Zendesk::tickets()->update($zend_ticket_id, $request_array);
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $comment_response, 'update', $system_ticket->ticket_id, null);
            return $comment_response;
        } catch (ApiResponseException $ex) {
            $zend_ticket_id = null;
            $comment_response = (object) array("error" => $ex->getMessage());
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $comment_response, 'update', $system_ticket->ticket_id, null);
            return $comment_response;
        } catch (Exception $exg) {
            $zend_ticket_id = null;
            $comment_response = (object) array("error" => $exg->getMessage());
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $comment_response, 'update', $system_ticket->ticket_id, null);
            return $comment_response;
        }
    }

    /**
     * Get user multiple tickets.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param array $tickets_array
     * @return object $tickets_detail
     */
    public function getZendTicketsByArrayIds($userRepo, $tickets_array)
    {
        $tickets_detail = "";
        $tickets_ids = [];
        try {
            if (count($tickets_array)) {
                foreach ($tickets_array as $tickets) {
                    $tickets_ids[] = $tickets['zendesk_ticket_id'];
                }
                return $tickets_detail = Zendesk::tickets()->findMany($tickets_ids);
            }
        } catch (Exception $ex) {
            $tickets_detail = (object) array("error" => $ex->getMessage());
            return $tickets_detail;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($tickets_ids), $tickets_detail, 'all_view', null, null);
        }
    }

    /**
     * Update ZenDesk User Name.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param int $user_id
     * @return array $user_array
     */
    public function updateZendUser($userRepo, $user_id, $user_array, $zend_id)
    {
        try {
            $request_array = [
                'id' => $zend_id,
                'name' => $user_array['first_name'] . ' ' . $user_array['last_name']
            ];
            $user_detail = Zendesk::users()->createOrUpdate($request_array);

            Event::fire(
                "zendesk.updateuser",
                serialize(['user_id' => $user_id, 'by_whom_id' => $user_id, 'email' => $user_array['email']])
            );
            return $user_detail;
        } catch (Exception $ex) {
            $user_detail = (object) array("error" => $ex->getMessage());
            return $user_detail;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $user_detail, 'user_update', null, null);
        }
    }

    /**
     * Update ZenDesk User Email.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param email $user_previous_details
     * @return email $new_email
     */
    public function updateZendUserEmail($userRepo, $user_previous_details, $new_email)
    {
        $request_array = ['value' => $new_email];
        try {
            $zend_user_detail = $this->checkZendUser($userRepo, $new_email);
            if (!isset($zend_user_detail->error)) {
                if (isset($zend_user_detail->users[0])) {
                    $zend['user_id'] = $zend_user_detail->users[0]->id;
                    $zend_user_update_email = $zend_user_detail;
                    return $zend;
                }
            }
            $zend_user_id = $user_previous_details->zendesk_user_id;
            $zend_user_identities = Zendesk::users($zend_user_id)->identities()->findAll();
            foreach ($zend_user_identities->identities as $identities) {
                if ($identities->type == 'email') {
                    $zend_email_identity_id = (int) $identities->id;
                }
            }

            $zend_user_update_email = Zendesk::users($zend_user_id)->identities($zend_email_identity_id)->update($zend_email_identity_id, $request_array);
            $zend['user_identities_data'] = $zend_user_update_email;
            Event::fire("zendesk.updateuseremail", serialize(['user_id' => $user_previous_details->id, 'by_whom_id' => \Auth::user()->id, 'email' => $user_previous_details->email]));

            return $zend;
        } catch (Exception $ex) {
            $zend_user_update_email = (object) array("error" => $ex->getMessage());
            return $zend_user_update_email;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $zend_user_update_email, 'email_update', null, null);
        }
    }

    /**
     * Get ZenDesk comments user by Zendesk User Ids.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param array $ids
     * @return array $user_name_array
     */
    public function getZendCommentsUsers($userRepo, $ids)
    {
        try {
            $user_ids_array = ['ids' => $ids];
            $users_information = Zendesk::users()->findMany($user_ids_array);
            $user_name_array = [];
            foreach ($users_information->users as $user_info) {
                $user_name_array[$user_info->id] = $user_info->name;
            }
            return $user_name_array;
        } catch (Exception $ex) {
            $users_information = (object) array("error" => $ex->getMessage());
            return $users_information;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($user_ids_array), $users_information, 'get_users', null, null);
        }
    }

    /**
     * Check ZenDesk user exist or not.
     *
     * @param email $email
     * @return object $zend_user_details | null
     */
    public function checkZendUser($userRepo, $email)
    {
        try {
            $email_query = ['query' => urlencode("type:user " . $email . "")];
            $user_detail = Zendesk::users()->search($email_query);

            return $user_detail;
        } catch (Exception $ex) {
            $user_detail = (object) array("error" => $ex->getMessage());
            return $user_detail;
        } finally {
            $this->saveTicketLogInfo($userRepo, json_encode($email_query), $user_detail, 'check_user', null, null);
        }
    }

    /**
     * Save Ticket Info Information.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param array $request
     * @param array $response
     * @param string $response_type
     * @param int|null $ticket_id
     * @param int|null $comment_id
     */
    protected function saveTicketLogInfo($userRepo, $request, $response, $response_type, $ticket_id, $comment_id)
    {
        if (isset($response->error)) {
            $response = $response->error;
            $status = 0;
        } else {
            $response = 'Success';
            $status = 1;
        }
        $arrTicketLog["request"] = $request;
        $arrTicketLog["response"] = $response;
        $arrTicketLog["req_type"] = $response_type;
        $arrTicketLog["status"] = $status;
        $arrTicketLog["ticket_id"] = $ticket_id;
        $arrTicketLog["comment_id"] = $comment_id;
        $arrTicketLog["ip_address"] = Request::ip();
        $arrTicketLog["created_at"] = \Helpers::getCurrentDateTime();
        $arrTicketLog["created_by"] = (int) \Auth::user()->id;

        $userRepo->saveTicketLogInfo($arrTicketLog);
    }

    /**
     * Create ZenDesk Comments.
     *
     * @param \App\Repositories\Entities\User\UserRepository $userRepo
     * @param \Illuminate\Http\Request $request
     * @param array $system_ticket
     * @return object $comment_response
     */
    public function updateZendTicketComment($userRepo, $request, $system_ticket, $author_id)
    {
        $request_array = [];
        try {
            $zend_user_id = $system_ticket->zendesk_lead_id;
            $zend_ticket_id = (int) $request->request->get('ticket_id');
            $request_array = [
                'status' => $request->request->get('status'),
                'comment' => ['body' => $request->request->get('your_comment'), 'author_id' => $author_id],
                'submitter_id' => $zend_user_id,
                'requester_id' => $zend_user_id
            ];
            $comment_response = Zendesk::tickets()->update($zend_ticket_id, $request_array);
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $comment_response, 'update', $system_ticket->ticket_id, null);
            return $comment_response;
        } catch (ApiResponseException $ex) {
            $zend_ticket_id = null;
            $comment_response = (object) array("error" => $ex->getMessage());
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $comment_response, 'update', $system_ticket->ticket_id, null);
            return $comment_response;
        } catch (Exception $exg) {
            $zend_ticket_id = null;
            $comment_response = (object) array("error" => $exg->getMessage());
            $this->saveTicketLogInfo($userRepo, json_encode($request_array), $comment_response, 'update', $system_ticket->ticket_id, null);
            return $comment_response;
        }
    }
}
