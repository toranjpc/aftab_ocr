<?php

namespace Database\Factories;

use Modules\Ocr\Models\TruckLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class TruckLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TruckLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $activity =['enter','exit'];
        // $door_names =['درب غربی', 'درب شرقی', 'درب شمالی', 'درب جنوبی'];

        return [
            'plate_num' => $this->numberToPlate(),
            'activity' => $activity[rand(0,1)],
            // 'camera_id' => rand(1,4),
            // 'door_name' => $door_names[rand(0,3)],
            'log_time' =>  $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }

    function numberToPlate() {
        do {
            $num3 = rand(100, 999);
         } while (strpos($num3, '0') !== false);

         do {
            $num2 = rand(10, 99);
         } while (strpos($num2, '0') !== false);

         do {
            $numIran = rand(10, 99);
         } while (strpos($numIran, '0') !== false);

        $letters = array( 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'ژ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل', 'م', 'ن', 'و', 'ی');
        $random_letter = $letters[rand(0, count($letters) - 1)];
        // $plate =  $num2 .' '. $random_letter .' '. $num3.' ' .'ایران'.' ' .$numIran  ;
        $plate =  $numIran  .'ein' . $num3 . $num2;

        return $plate;



    }

}
