<?php

use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\Opportunity;

/**
 * Class OpportunitySeeder
 */
class OpportunitySeeder extends Seeder
{
    /**
     * OpportunitySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(Opportunity::class);
        $this->ModelRepositoryObj = App::make(OpportunityRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        $OpportunityObjArr = parent::run();
        /** @var Opportunity $OpportunityObj */
        foreach ($OpportunityObjArr as $OpportunityObj)
        {
            /** @var Opportunity $OpportunityObj */
            $this->ModelRepositoryObj->update(
                [
                    'client_category_id' => $OpportunityObj->property->client->clientCategories->first()->id,
                ],
                $OpportunityObj->id
            );
            $OpportunityObj->assignedToUser->comment($OpportunityObj, Seeder::getFakerObj()->sentence());
            $OpportunityObj->createdByUser->comment($OpportunityObj, Seeder::getFakerObj()->sentence());
        }
        return $OpportunityObjArr;
    }
}