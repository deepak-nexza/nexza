<?php namespace App\B2c\Repositories\Entities\Document;

use Storage;
use App\B2c\Repositories\Models\Master\Document;
use App\B2c\Repositories\Contracts\UserInterface;
use App\B2c\Repositories\Models\ApplicationDocument;
use App\B2c\Repositories\Contracts\DocumentInterface;
use App\B2c\Repositories\Models\ApplicationDocRequested;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Contracts\Traits\CommonRepositoryTraits;
use App\B2c\Repositories\Entities\Application\Exceptions\InvalidDataTypeExceptions;
use Biz2Credit\Yodlee\Repositories\Models\FinanceDataLog;
use App\B2c\Repositories\Models\MonitoringDocument;

class DocumentRepository extends BaseRepositories implements DocumentInterface
{
    use CommonRepositoryTraits;

    protected $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
        parent::__construct();
    }

    /**
     * Create method
     *
     * @param array $attributes
     * @since 0.1
     */
    protected function create(array $attributes)
    {
        Document::create($attributes);
    }

    /**
     * Update method
     *
     * @param array $attributes
     * @since 0.1
     */
    protected function update(array $attributes, $app_id)
    {
    }

    /**
     * Save Document
     *
     * @param array $docData document data
     *
     * @return integer document id
     *
     * @since 0.1
     */
    public function saveDocument(array $docData)
    {
        return ApplicationDocument::saveDocument($docData);
    }

    /**
     * Delete document by document id and user id
     *
     * @param integer $document_id document id
     * @param integer $user_id user id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function deleteDocument($document_id)
    {
        return ApplicationDocument::deleteDocument($document_id);
    }

    /**
     * Update document encrypt field
     *
     * @param type $document_id
     * @param type $varFileEncName
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function updateEncryptID($document_id, $varFileEncName, $fileSize, $filesource)
    {
        return ApplicationDocument::updateEncryptID($document_id, $varFileEncName, $fileSize, $filesource);
    }

    /**
     * Download document by encrypt id
     *
     * @param string $doc_encrypt_id
     *
     * @return type
     *
     * @since 0.1
     */
    public function getDownloadFile($doc_encrypt_id)
    {
        return ApplicationDocument::getDownloadFile($doc_encrypt_id);
    }

    /**
     * Get document path for a user
     *
     * @param  mixed $userId Should be an integer
     * @return mixed boolean for failure | string for success
     */
    public function getUserFolder($userId = false)
    {
        $docRootPath = storage_path() . '/appDocuments';

        // Get logged in user data
        $user = $this->user->getAuthUserData();

        // If user data not found, return false
        if (!$user) {
            return false;
        }

        // If loggedin user and a customer, return his/her own folder path
        //if ($this->user->isCustomer($user)) {
        // return $docRootPath . '/' . $user->id;
        //}
        // If a user is requestin the path other thatn a user
        // and has ability to access users area,
        // return the path for the $userId requested
        // if (is_integer($userId) && $user->can('view-customer-documents')) {
        //}
        return $docRootPath;
        // In all other case return false
        //return false;
    }

    /**
     * Create a document folder for a user
     *
     * @param integer $userId
     * @return boolean
     */
    public function createUserFolder($userId = false)
    {


        // If not a authenticated user, try to get the object on $userId passed
        $requestedUser = false;
        if (is_integer($userId)) {
            $requestedUser = $this->user->find($userId);
        }

        // If a user is requesting the path other than the user himself/herself
        // and has ability to access user document, return the path for the $userId requested
        if ($requestedUser) {
            return Storage::makeDirectory($userId);
        }
        // In all other case return false
        return false;
    }

    /**
     * Check Application  document exists or not by application id
     *
     * @param integer $app_id
     * @return  boolean
     */
    public function checkApplicationDocument($app_id,$doc_id)
    {
        return ApplicationDocument::checkApplicationDocument((int) $app_id, (int) $doc_id);
    }


    /**
     * Save Request Document
     *
     * @param array $arrData
     * @param int $id
     *
     * @return boolean
     *
     */
    public function saveRequestedDoc($arrData, $id=null)
    {
        return ApplicationDocRequested::saveRequestedDoc($arrData, $id);
    }


    /**
     * Get Application requested document list
     *
     * @return array
     */
    public function getRequiredDoc()
    {
        return Document::getRequiredDoc();
    }


    /**
     * Get document list by category
     *
     * @return array
     */
    public function getDocsByCategory($category_id)
    {
        return Document::getDocsByCategory((int) $category_id);
    }

    /**
     * Get document all lists
     *
     * @param string $additional_doc
     *
     * @return array
     */
    public function getDocumentList($additional_doc)
    {
        return Document::getDocumentList($additional_doc);
    }

    /**
     * Check is document exists for an application
     *
     * @param integer $app_id
     * @param string $doc_name
     *
     * @return mixed
     *
     * @throws InvalidDataTypeExceptions
     */
    public function isDocumentExistsForApplication($app_id, $doc_name)
    {

         return ApplicationDocRequested::isDocumentExistsForApplication((int) $app_id, $doc_name);
    }

    /**
     * Added new document
     *
     * @param array $attributes
     *
     * @rerurn object
     */
    public function addDocument(array $attributes)
    {
        $objDoc = Document::create($attributes);

        return $objDoc->id;
    }

    /**
     * Get document by doc type
     *
     * @param string $docType
     *
     * @return array
     */
    public function getDocListByDocType($docType)
    {
        return Document::getDocListByDocType($docType);
    }
    
    /**
     * Get document by overdraft condition
     *
     * @param string $docType
     *
     * @return array
     */
    public function getDocListWithOverdraftProduct($docType)
    {
        return Document::getDocListWithOverdraftProduct($docType);
    }

    /**
     * Get requested document by doc type
     *
     * @param integer $appId
     * @param integer $docType
     *
     * @return mixed   Integer application id | Boolean false
     *
     */
    public function getRequestedDocsByType($appId, $docType )
    {
        return ApplicationDocRequested::getRequestedDocsByType($appId, $docType);
    }

    /**
     * Check exit requested document
     *
     * @param integer $appId
     * @param integer $docType
     * @param integer $docId
     *
     * @return mixed   Integer application id | Boolean false
     *
     */
    public function checkExitRequestDocs($appId, $docType, $docId)
    {
        return ApplicationDocRequested::checkExitRequestDocs($appId, $docType, $docId);
    }

    /*
     * Update request document status
     *
     * @param integer $appId
     * @param array $reqDocIds
     * @return mixed
     *
     */
    public function updateRequestDocs($appId, $reqDocIds)
    {
        return ApplicationDocRequested::updateRequestDocs($appId, $reqDocIds);
    }

    /*
     * Get selected document
     * @param integer $appId
     * @param array $reqDocId
     *
     * @return mixed
     *
     */
    public function getSelectedDocs($appId, $docType)
    {
        return ApplicationDocRequested::getSelectedDocs($appId, $docType);
    }

    /**
     * Get app requested document
     *
     * @param integer $appId
     * @param integer $docType
     *
     * @return mixed   Integer application id | Boolean false
     *
     */
    public function getAllRequestedDocs($appId, $docType )
    {
        return ApplicationDocRequested::getAllRequestedDocs($appId, $docType);
    }

    /**
     * Get document owner type
     *
     * @param integer $appId
     * @param integer $docType
     * @param integer $ownerType
     *
     * @return mixed   Integer application id | Boolean false
     *
     */
    public function getDocumentOwnerType($app_id, $docType, $ownerCatType)
    {

        return ApplicationDocRequested::getDocumentOwnerType($app_id, $docType, $ownerCatType);
    }

    /**
     * update document
     *
     * @param integer $appDocId
     * @param array $attribute
     * @return mixed
     */
    public function updateDocument($appDocId, $attribute=[])
    {
        return ApplicationDocument::updateDocument((int) $appDocId, $attribute);
    }

    /**
     * Get document by doc type id and app id
     *
     * @param integer $appId
     * @param integer $docTypeId
     * @return mixed
     */
    public function getAllPersonalTaxDoc($appId, $docTypeId)
    {
        return ApplicationDocRequested::getAllPersonalTaxDoc((int) $appId, $docTypeId);
    }

    /**
     * Get master document name
     *
     * @param integer $appDocId
     * @return array
     */
    public function getMasterCollectionById($appDocId)
    {
        return Document::getMasterCollectionById((int) $appDocId);
    }
    
    /**
     * Get requested document by app id, req doc id, doc id
     *
     * @param integer $app_id
     * @param integer $req_doc_id
     * @param integer $doc_id
     *
     * @return array
     */
    public function getReqDocDetailByAppIdReqIdAndDocId($app_id, $req_doc_id, $doc_id)
    {
        return ApplicationDocRequested::getReqDocDetailByAppIdReqIdAndDocId((int) $app_id, (int) $req_doc_id, (int) $doc_id);
    }
    
    /**
     * Get yodlee response log detail
     *
     * @param integer $log_id
     *
     * @return array
     */
    public function getYodleeLogDetail($log_id)
    {
        return FinanceDataLog::getYodleeLogDetail($log_id);
    }
    
     /**
     * Save Document
     *
     * @param array $docData document data
     *
     * @return integer document id
     *
     * @since 0.1
     */
    public function saveMonitoringDocument(array $docData)
    {
        return MonitoringDocument::saveMonitoringDocument($docData);
    }
    
    /**
     * Get App Doc By Encrypted Id
     *
     * @param string $doc_encrypt_id
     * @return array
     */
    public function getAppDocByEncryptedId($doc_encrypt_id)
    {
        return ApplicationDocument::getAppDocByEncryptedId($doc_encrypt_id);
    }

    /**
     * Get source of uploaded files
     * 
     * @param integer $appId
     * @return mixed
     */
    public function getUploadedFilesSource($appId)
    {
        return ApplicationDocument::getUploadedFilesSource($appId);
    }
    
    /**
     * check scanned pdf
     * 
     * @param integer $appId
     * @return mixed
     */
    public function getAllnonNativeDoc($appId,$is_pdf)
    {
        return ApplicationDocument::getAllnonNativeDoc($appId,$is_pdf);
    }     

    /**
     * Delete By Doc Type 
     * 
     * @param integer $docId
     * @return mixed
     */
    public function deleteDocReqByDocType($appId, $docType)
    {
        return ApplicationDocRequested::deleteDocReqByDocType($appId, $docType);
    }
    
      /**
     * Delete document by document id and user id
     *
     * @param integer $document_id document id
     * @param integer $user_id user id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function countNotClassified($app_id,$status)
    {
        return ApplicationDocument::countNotClassified( (int) $app_id , $status);
    }
    
      /**
     * Delete document by document id and user id
     *
     * @param integer $document_id document id
     * @param integer $user_id user id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function deleteDocumentNotClassified($app_id)
    {
        return ApplicationDocument::deleteDocumentNotClassified( (int) $app_id );
    }
}