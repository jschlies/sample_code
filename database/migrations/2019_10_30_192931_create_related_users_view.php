<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelatedUsersView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("create or replace view vw_relatedUsers
                                as
                                  select  -- distinct
                                       ru.user_id,
                                       rt.client_id,
                                       rt.id,
                                       ru.related_object_id,
                                       rt.name,
                                       ifnull(replace(ifnull(rt.description,''), \"\'\", \"\"),'') as 'description',
                                       rt.related_object_type,
                                       rt.related_object_subtype,
                                       rt.created_at,
                                       rt.updated_at,
                                
                                       concat('[',
                                              (select GROUP_CONCAT( '{\"user_id\":\"',ifnull(ub.user_id,''), '\", \"related_user_id\":\"', ifnull(ub.id, ''), '\"}')
                                               from related_users ub
                                               where ub.related_user_type_id =  rt.id --  28 --
                                               and ub.related_object_id = ru.related_object_id) -- 5823 --
                                              , ']') as 'users'
                                
                                  from related_user_types rt
                                  left join related_users      ru on rt.id = ru.related_user_type_id
                                  join users u on ru.user_id = u.id and u.is_hidden = 0");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new GeneralException('Migration down() not supported');
    }
}
