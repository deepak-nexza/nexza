<?php

namespace App\B2c\Repositories\Libraries\Storage\Crypt;

use Exception;
use Aws\Kms\KmsClient;
use Aws\Kms\Exception\KmsException;
use App\B2c\Repositories\Contracts\Traits\AwsSdkTrait;
use App\B2c\Repositories\Libraries\Storage\Contract\ErrorHandlerTrait;

class AwsKms
{

    use AwsSdkTrait, ErrorHandlerTrait;

    /**
     * KMS factory
     *
     * @var KmsClient::Factory()
     */
    protected $kms;

    /**
     * KMS Key id
     *
     * @var string
     */
    private $keyId;

    /**
     * Max number of retry to encrypt/decrypt.
     *
     * @var integer
     */
    private $maxRetry = 3;

    /**
     * Class instance
     */
    public function __construct()
    {
        try {
            $this->kms = new KmsClient($this->getOptions());
            $this->keyId = config('filesystems.disks.s3.kms_key_id');
        } catch (KmsException $ex) {
            $this->error($ex);
        } catch (Exception $ex) {
            $this->error($ex);
        }
    }

    /**
     * Encrypts data in plain text
     *
     * @param string $plaintext
     * @return mixed string|boolean
     */
    public function encrypt($plaintext)
    {
        $result = true;
        $retry = 0;

        while ($retry++ < $this->maxRetry) {
            try {
                $result = $this->kms->encrypt([
                    'KeyId' => $this->keyId,
                    'Plaintext' => $plaintext
                ]);

                return base64_encode($result->get('CiphertextBlob'));
            } catch (KmsException $ex) {
                $result = $this->error($ex);
            } catch (Exception $ex) {
                $result = $this->error($ex);
            }

            // If failed, wait 1 second before the next call to KMS.
            sleep(1);
        }

        return $result;
    }

    /**
     * Decrypts cryptic data
     *
     * @param string $cryptic
     * @return mixed string|boolean
     */
    public function decrypt($cryptic)
    {
        $result = true;
        $retry = 0;
        while ($retry++ < $this->maxRetry) {
            try {
                $result = $this->kms->decrypt([
                    'CiphertextBlob' => base64_decode($cryptic)
                ]);

                return $result->get('Plaintext');
            } catch (KmsException $ex) {
                $result = $this->error($ex);
            } catch (Exception $ex) {
                $result = $this->error($ex);
            }

            // If failed, wait 1 second before the next call to KMS.
            sleep(1);
        }

        return $result;
    }
}