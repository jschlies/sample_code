<?php

namespace App\Waypoint;

use App\Waypoint\Models\Attachment;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait GetEntityTagsTrait
 * @package App\Waypoint\Models
 *
 * NOTE NOTE NOTE
 * This trait exists because the User, permission and role models
 * extend App\Waypoint\Models\Entrust\User which extends App\Waypoint\Models\Entrust which extends blah blah.
 *          the point is that if we want to add base functionality to all models, we need to use this trait
 *
 */
trait HasAttachment
{

    use \Bnb\Laravel\Attachments\HasAttachment;

    /**
     * Get the attachments relation morphed to the current model class
     *
     * @return MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }

    /**
     * @param UploadedFile|string $fileOrPath
     * @param array $options Set attachment options : title, description, key, disk
     *
     * @return Attachment|null
     */
    public function attach($fileOrPath, $options = [])
    {
        if ( ! is_array($options))
        {
            throw new Exception('Attachment options must be an array');
        }

        if (empty($fileOrPath))
        {
            throw new Exception('Attached file is required');
        }

        $options = array_only($options, config('attachments.attributes'));

        if ( ! empty($options['key']) && $attachment = $this->attachment($options['key']))
        {
            $attachment->delete();
        }

        /** @var Attachment $attachment */
        $attachment = new Attachment($options);

        if ($fileOrPath instanceof UploadedFile)
        {
            $attachment->fromPost($fileOrPath);
        }
        else
        {
            $attachment->fromFile($fileOrPath);
        }

        if ($attachment = $this->attachments()->save($attachment))
        {
            return $attachment;
        }

        return null;
    }
}
