<?php

use App\Waypoint\Models\TenantIndustry;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class TenantAndLeasing
 */
class TenantAndLeasing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'tenant_industries',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('client_id')->unsigned()->index();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('tenant_industry_category')->nullable();
                $table->timestamps();

                $table->unique(['name', 'tenant_industry_category', 'client_id'], 'tenant_industries_nticid_unique');
            });

        Schema::table(
            'tenant_industries',
            function (Blueprint $table)
            {
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            }
        );

        Schema::create(
            'tenant_attributes',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('client_id')->unsigned()->index();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('tenant_attribute_category')->nullable();
                $table->timestamps();

                $table->unique(['name', 'tenant_attribute_category', 'client_id'], 'tenant_attributes_ntacid_unique');
            });

        Schema::table(
            'tenant_attributes',
            function (Blueprint $table)
            {
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            }
        );

        Schema::create(
            'tenants',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('client_id')->unsigned()->index();
                $table->integer('tenant_industry_id')->unsigned()->index();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();

                $table->unique(['name', 'client_id']);
            });

        Schema::table(
            'tenants',
            function (Blueprint $table)
            {
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                $table->foreign('tenant_industry_id')->references('id')->on('tenant_industries')->onDelete('cascade');
            }
        );
        Schema::create(
            'tenant_tenant_attributes',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('tenant_id')->unsigned()->index();
                $table->integer('tenant_attribute_id')->unsigned()->index();
                $table->timestamps();

                $table->unique(['tenant_id', 'tenant_attribute_id']);
            }
        );

        Schema::table(
            'tenant_tenant_attributes',
            function (Blueprint $table)
            {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('tenant_attribute_id')->references('id')->on('tenant_attributes')->onDelete('cascade');
            }
        );

        Schema::create(
            'suite_tenants',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('tenant_id')->unsigned()->index();
                $table->integer('suite_id')->unsigned()->index();
                $table->timestamps();

                $table->unique(['tenant_id', 'suite_id']);
            }
        );
        Schema::table(
            'suite_tenants',
            function (Blueprint $table)
            {
                $table->foreign('tenant_id')->references('id')->nullable()->on('tenants')->onDelete('cascade');
                $table->foreign('suite_id')->references('id')->nullable()->on('suites')->onDelete('cascade');
            }
        );

        Schema::create(
            'lease_tenants',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('tenant_id')->unsigned()->index();
                $table->integer('lease_id')->unsigned()->index();
                $table->timestamps();

                $table->unique(['tenant_id', 'lease_id']);
            }
        );
        Schema::table(
            'lease_tenants',
            function (Blueprint $table)
            {
                $table->foreign('tenant_id')->references('id')->nullable()->on('tenants')->onDelete('cascade');
                $table->foreign('lease_id')->references('id')->nullable()->on('leases')->onDelete('cascade');
            }
        );

        $client_results = DB::select(
            DB::raw(
                '
                    SELECT * FROM clients
                '
            )
        );

        foreach ($client_results as $client_result)
        {
            if ($client_result->id == 1)
            {
                continue;
            }
            foreach (TenantIndustry::$tenant_industry_default_name_arr as $tenant_industry_value)
            {
                DB::insert(
                    DB::raw(
                        '
                            INSERT into tenant_industries
                                set    
                                    name = :NAME,
                                    description            = :DESCRIPTION,
                                    tenant_industry_category   = :TENANT_TYPE_CATEGORY,
                                    client_id           = :CLIENT_ID,
                                    created_at = now(),
                                    updated_at = now()
                        '
                    ),
                    [
                        'NAME'                 => $tenant_industry_value,
                        'DESCRIPTION'          => 'Created via migration at ' . Carbon::now()->format('Y-m-d H:i:s'),
                        'TENANT_TYPE_CATEGORY' => TenantIndustry::TENANT_TYPE_CATEGORY_PRIMARY_INDUSTRTY,
                        'CLIENT_ID'            => $client_result->id,
                    ]
                );
            }

            $default_tenant_industry_result = DB::select(
                DB::raw(
                    '
                            SELECT * FROM tenant_industries
                                WHERE    
                                     client_id = :CLIENT_ID AND
                                     name            = :NAME
                        '
                ),
                [
                    'NAME'      => TenantIndustry::TENANT_TYPE_DEFAULT,
                    'CLIENT_ID' => $client_result->id,
                ]
            );

            DB::UPDATE(
                DB::raw(
                    '
                            UPDATE tenants
                                SET    
                                     tenant_industry_id = :TENANT_TYPE_ID,
                                     updated_at = now()
                                WHERE
                                     client_id = :CLIENT_ID
                        '
                ),
                [
                    'TENANT_TYPE_ID' => $default_tenant_industry_result[0]->id,
                    'CLIENT_ID'      => $client_result->id,
                ]
            );

        }
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
