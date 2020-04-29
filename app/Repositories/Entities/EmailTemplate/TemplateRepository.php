<?php

namespace App\B2c\Repositories\Entities\EmailTemplate;

use App\B2c\Repositories\Models\SendMail;
use App\B2c\Repositories\Contracts\Traits\AuthTrait;
use App\B2c\Repositories\Models\EmailTemplateTag;
use App\B2c\Repositories\Models\Master\EmailTemplate;
use App\B2c\Repositories\Models\Master\EmailCategory;
use App\B2c\Repositories\Contracts\TemplateInterface;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Contracts\Traits\CommonRepositoryTraits;

/**
 * Report repository class
 */
class TemplateRepository extends BaseRepositories implements TemplateInterface
{

    use CommonRepositoryTraits,
        AuthTrait;

    /**
     * Create method
     *
     * @param array $attributes
     */
    protected function create(array $attributes)
    {
    }

    /**
     * Update method
     *
     * @param array $attributes
     */
    protected function update(array $attributes, $id)
    {
    }

    /**
     * Get System Generated Email Templates All List
     *
     * @return array
     */
    public function getSystemTemplatesList()
    {
        return EmailTemplate::getSystemTemplatesList();
    }

    /**
     * Get User defined Email Templates All List
     *
     * @return array
     */
    public function getUserTemplatesList()
    {
        return EmailTemplate::getUserTemplatesList();
    }

    /**
     * Get User defined Email Templates triggers All List
     *
     * @return array
     */
    public function getTriggersList()
    {
        return EmailCategory::getTriggersList();
    }

    /**
     * updatet System emnail tempalte
     *
     * @param array $trigger_id
     * @param integer $input
     * @return Integer or boolean
     */
    public function saveSystemTemplates($trigger_id, $input)
    {
        return EmailTemplate::saveSystemTemplates($trigger_id, $input);
    }

    /**
     * Get Email template by Template Id
     * @param Integer $template_cat_id
     * @return Array
     */
    public function getTemplateById($template_cat_id)
    {
        return EmailTemplate::getTemplateById((int) $template_cat_id);
    }

    /**
     * Get Email template by Template Id
     * irrespective of status
     *
     * @param Integer $template_cat_id
     * @return type
     */
    public function getTemplate($template_cat_id)
    {
        return EmailTemplate::getTemplate((int) $template_cat_id);
    }

    /**
     * Get User defined email template by id
     * @param Integer $template_id
     * @return array
     */
    public function getUserTemplate($template_id)
    {
        return EmailTemplate::getUserTemplate((int) $template_id);
    }

    /**
     * Save or Update user defined Templates
     * @param array $input
     * @param Integer $template_id
     * @return Integer or Boolean
     */
    public function saveUserTemplates(array $input, $template_id)
    {

        return EmailTemplate::saveUserTemplates($template_id, $input);
    }

    /**
     * Deleting user defined templates
     *
     * @param Integer $template_id
     * @return Boolean
     * @throws BlankDataExceptions
     * @throws InvalidDataTypeExceptions
     */
    public function deleteUserTemplate($template_id)
    {
        return EmailTemplate::deleteUserTemplate($template_id);
    }

    /**
     * Update Status of template Activate and De-Activate
     *
     * @param Integer $template_id
     * @param Integer $status
     * @return boolean
     */
    public function updateTemplateStatus($template_id, $status)
    {
        return EmailTemplate::updateTemplateStatus($template_id, $status);
    }
    
     /**
     * Get User template list according to logged in user
     *
     * @param integer $logged_in_id
     * @return array
     */
    public function getTemplateByloggedIn($logged_in_id, $template_type = null)
    {
        return EmailTemplate::getTemplateByloggedIn((int) $logged_in_id, (int) $template_type);
    }
    
    /**
     * Get all email template tag
     *
     * @return mixed
     */
    public function getEmailTemplateTags()
    {
        return EmailTemplateTag::getEmailTemplateTags();
    }

    /**
     * return tags information
     *
     * @param array $tagArr
     * @return type
     */
    public function getEmailTemplateTagInfo($tagArr)
    {
         return EmailTemplateTag::getEmailTemplateTagInfo($tagArr);
    }

     /**
     * Get the content of send mail
     *
     * @param int $sendmail_id
     * @return Boolean
     */
    public function getSendMailContentById($sendmail_id, $select = null)
    {
        return SendMail::getSendMailContentById($sendmail_id, $select);
    }
    
    /**
     * Get email templates
     * @param array $attributes
     * @return mixed
     */
    public function getEmailTemplates($attributes)
    {
        return EmailTemplate::getEmailTemplates($attributes);
    }
    
    /**
     * Get email template
     * @param string $title
     * @return mixed
     */
    public function getEmailTemplate($title)
    {
        return EmailTemplate::getEmailTemplate($title);
    }
}
