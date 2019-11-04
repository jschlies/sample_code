<?php

namespace App\Waypoint\Tests\Generated;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Seeder;
use App\Waypoint\Models\CommentMention;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCommentMentionTrait
{
    /**
     * Create fake instance of CommentMention and save it in database
     *
     * @param array $comment_mentions_arr
     * @return CommentMention
     */
    public function makeCommentMention($comment_mentions_arr = [])
    {
        $theme = $this->fakeCommentMentionData($comment_mentions_arr);
        return $this->CommentMentionRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CommentMention
     *
     * @param array $comment_mentions_arr
     * @return CommentMention
     */
    public function fakeCommentMention($comment_mentions_arr = [])
    {
        return new CommentMention($this->fakeCommentMentionData($comment_mentions_arr));
    }

    /**
     * Get fake data of CommentMention
     *
     * @param array $comment_mentions_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCommentMentionData($comment_mentions_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($comment_mentions_arr);
        return $factory->raw(CommentMention::class, $comment_mentions_arr, $factory_name);
    }
}