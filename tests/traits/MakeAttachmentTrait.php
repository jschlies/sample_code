<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Seeder;

/**
 * @codeCoverageIgnore
 */
trait MakeAttachmentTrait
{
    /**
     * Get fake data of Attachment
     *
     * @param array $_fields
     * @return array
     */
    public function fakeAttachmentData()
    {
        return [
            'path'         => base_path('storage/app/temp_attachments/README'),
            'originalName' => Seeder::getFakerObj()->words(4, true),
            'mimeType'     => 'application/text',
            'size'         => filesize(base_path('/storage/app/temp_attachments/README')),
        ];
    }
}