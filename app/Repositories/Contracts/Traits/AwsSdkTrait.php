<?php

namespace App\B2c\Repositories\Contracts\Traits;

trait AwsSdkTrait
{

    /**
     * Get options to create a AWS service client.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
        ];

        if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
            $options['credentials'] = [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ];
        }

        return $options;
    }
}
