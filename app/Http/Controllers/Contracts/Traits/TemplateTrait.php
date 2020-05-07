<?php

namespace App\Http\Controllers\Contracts\Traits;

use Helpers;
use App\Http\Requests\EmailTemplateRequest;
use App\Http\Requests\UserTemplateRequest;

trait TemplateTrait
{

    /**
     * Display List of all templates system and user defined
     *
     * @return \Illuminate\View\View
     */
    public function showEmailTemplates()
    {
        try {
            $system_generated_templates = $this->template->getSystemTemplatesList();
            $user_defined_templates     = $this->template->getUserTemplatesList();
            return view($this->templateView . '.email_templates.email_template')
                    ->with('system_generated_templates', $system_generated_templates)
                    ->with('user_defined_templates', $user_defined_templates);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * Display Edit System templates by selection of trigger
     *
     * @return \Illuminate\View\View
     */
    public function editSystemTemplates()
    {
        try {
            $template_list        = Helpers::getAllEmailTemplateList()->toArray();
            $template_detail = [];
            return view($this->templateView . '.email_templates.edit_system_template')
                    ->with('template_list', $template_list)
                    ->with('template_detail', $template_detail);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * Update System Email Template by selected trigger
     *
     * @param App\Http\Request\EmailTemplateRequest $request
     * @return \Illuminate\Routing\Redirector
     */
    public function saveSystemTemplates(EmailTemplateRequest $request)
    {
        try {
            $pkey  =  $request->input('email_cat_id');
            $input['reciepient_cc'] = $request->input('reciepient_cc');
            $input['en_mail_subject']  = $request->input('mail_subject');
            $input['en_mail_body']     = $request->input('mail_body');
            $updated_id             = $this->template->saveUserTemplates($input, $pkey);
            //dd($input);
            if ($updated_id) {
                session()->flash('message', 'Template updated successfully');
                return redirect(route($this->routePrifix . 'email_template'));
            }
        } catch (Exception $e) {
            dd($e->getMessages());
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * View System Templates by trigger Id
     *
     * @return \Illuminate\View\View
     */
    public function viewSystemTemplates()
    {
        try {
            $template_id = request()->get('template_id');
            if (!Helpers::isNaturalNumber($template_id)) {
                return abort(400);
            }
            $template_detail = $this->template->getTemplate((int) $template_id);
            return view($this->templateView . '.email_templates.view_email_template')
                    ->with('template_detail', $template_detail);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * view System Template by Id
     *
     * @param Integer $template_id
     * @return \Illuminate\View\View
     */
    public function viewUserTemplates()
    {
        try {
            $template_id = request()->get('template_id');
            if (!Helpers::isNaturalNumber($template_id)) {
                return abort(400);
            }
            $template_detail = $this->template->getUserTemplate((int) $template_id);
            return view($this->templateView . '.email_templates.view_user_template')
                    ->with('template_detail', $template_detail);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * Create User defined templates
     *
     * @param int $template_id
     * @return \Illuminate\View\View
     */
    public function createTemplates()
    {
        $template_id = request()->request->get('id');
      
        try {
            $fetchData = $this->template->getUserTemplate((int) $template_id);
            if ($template_id > 0 && $fetchData === false) {
                abort(400);
            }
            return view($this->templateView . '.email_templates.create_template')
                    ->with('fetchData', $fetchData);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * Save Templates
     *
     * @param App\Http|Request\UserTemplateRequest $request
     * @param int $edit_template_id
     * @return \Illuminate\Routing\Redirector
     */
    public function saveTemplates(UserTemplateRequest $request)
    {
        try {
            $user_template = [];
            $template_id   = (int) $request->input('edit_tmp_id');
            if (empty($template_id)) {
                $template_id = null;
            }
            $user_template          = $this->template->getUserTemplate($template_id);
            $input['mail_title']    = $request->input('mail_title');
            $input['email_cat_id']  = config('b2c_common.USER_DEFINED');
            $input['en_reciepient_cc'] = $request->input('reciepient_cc');
            $input['en_mail_subject']  = $request->input('mail_subject');
            $input['en_mail_body']     = $request->input('mail_body');
            $input['template_type'] = config('b2c_common.USER_DEFINED');

            $store_dynamic_tag = '';
            $all_tags          = $this->template->getEmailTemplateTags();
            foreach ($all_tags as $all_tag) {
                $dynamic_tag = "#" . $all_tag->dynamic_tag;
                if (strpos($input['mail_body'], $dynamic_tag)) {
                    $store_dynamic_tag .= $dynamic_tag . ",";
                }
            }
            $input['dynamic_tag'] = rtrim($store_dynamic_tag, ",");
            $this->template->saveUserTemplates($input, (int) $template_id);
            if ($template_id) {
                session()->flash('message', 'Template updated successfully');
            } else {
                session()->flash('message', 'Template created successfully');
            }
            return redirect(route($this->routePrifix . 'email_template'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * view of calendar
     *
     * @return type
     */
    public function myCalendar()
    {
        try {
            return view($this->templateView . '.email_templates.my_calendar');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    public function getSendMailContent()
    {
        try {
            $sendmail_id = (int) request()->query('sendmail_id');
            $resultObj   = $this->template->getSendMailContentById($sendmail_id);
            return view($this->templateView . '.affiliate.view_sendmail')->with('results', $resultObj);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
}
