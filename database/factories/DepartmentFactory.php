<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = Department::class;

    /**
     * قائمة بأسماء الأقسام الطبية الشائعة.
     * يمكن إضافة المزيد حسب الحاجة.
     *
     * @var array
     */
    public static $departments = [
        [
            'name' => [
                'en' => 'Cardiology',
                'ar' => 'طب القلب',
            ],
            'description' => [
                'en' => 'This department specializes in diagnosing and treating heart diseases.',
                'ar' => 'يختص هذا القسم بتشخيص وعلاج أمراض القلب.',
            ],
        ],
        [
            'name' => [
                'en' => 'Neurology',
                'ar' => 'طب الأعصاب',
            ],
            'description' => [
                'en' => 'Deals with disorders of the nervous system, including the brain and spinal cord.',
                'ar' => 'يعالج اضطرابات الجهاز العصبي، بما في ذلك الدماغ والحبل الشوكي.',
            ],
        ],
        [
            'name' => [
                'en' => 'Pediatrics',
                'ar' => 'طب الأطفال',
            ],
            'description' => [
                'en' => 'Provides medical care for infants, children, and adolescents.',
                'ar' => 'يقدم رعاية طبية للرضع والأطفال والمراهقين.',
            ],
        ],
        [
            'name' => [
                'en' => 'Dermatology',
                'ar' => 'الأمراض الجلدية',
            ],
            'description' => [
                'en' => 'Focuses on the diagnosis and treatment of skin conditions.',
                'ar' => 'يركز على تشخيص وعلاج حالات الجلد المختلفة.',
            ],
        ],
        [
            'name' => [
                'en' => 'Orthopedics',
                'ar' => 'جراحة العظام',
            ],
            'description' => [
                'en' => 'Treats conditions related to bones, joints, and muscles.',
                'ar' => 'يعالج الحالات المتعلقة بالعظام والمفاصل والعضلات.',
            ],
        ],
        [
            'name' => [
                'en' => 'Urology',
                'ar' => 'المسالك البولية',
            ],
            'description' => [
                'en' => 'Concerned with urinary tract diseases and male reproductive organs.',
                'ar' => 'يعنى بأمراض الجهاز البولي والأعضاء التناسلية الذكرية.',
            ],
        ],
        [
            'name' => [
                'en' => 'Pulmonology',
                'ar' => 'طب الرئة',
            ],
            'description' => [
                'en' => 'Specializes in lung and respiratory system diseases.',
                'ar' => 'متخصص في أمراض الرئة والجهاز التنفسي.',
            ],
        ],
        [
            'name' => [
                'en' => 'Gastroenterology',
                'ar' => 'أمراض الجهاز الهضمي',
            ],
            'description' => [
                'en' => 'Focuses on the digestive system and related disorders.',
                'ar' => 'يركز على الجهاز الهضمي والاضطرابات المرتبطة به.',
            ],
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    private static $index = 0;
    public function definition(): array
    {
        // لحل مشكلة "Maximum retries of 10000 reached without finding a unique value":
        // تحدث هذه المشكلة عندما تحاول إنشاء عدد من السجلات أكبر من عدد القيم الفريدة
        // المتوفرة في القائمة الثابتة 'medicalDepartmentNames'.
        // فمثلاً، إذا كانت القائمة تحتوي على 8 أسماء (كما هو الحال حالياً)، وحاولت إنشاء 9 أقسام،
        // فإن Faker لن يتمكن من العثور على اسم تاسع فريد من هذه القائمة المحدودة.

        // للحفاظ على نفس الأسماء دون تكرار، يجب عليك التأكد من أن عدد الأقسام
        // التي تقوم بإنشائها لا يتجاوز عدد الأسماء المتوفرة في القائمة
        // 'self::$medicalDepartmentNames'.
        // في حالتك الحالية، تحتوي القائمة على 8 أسماء.
        // لذا، عند استدعاء Department::factory() في DatabaseSeeder،
        // يجب أن يكون العدد 8 أو أقل.
        // على سبيل المثال:
        // Department::factory(8)->create(); // هذا سيستخدم كل الأسماء مرة واحدة بالضبط
        // Department::factory(5)->create(); // هذا سيستخدم 5 أسماء فريدة عشوائية من القائمة


        // return [
        //     // تستخدم unique()->randomElement() لضمان أن كل اسم قسم يتم استخدامه مرة واحدة فقط
        //     // من القائمة المحددة لكل عملية seeding، طالما أن عدد السجلات المطلوبة لا يتجاوز
        //     // حجم القائمة.
        //     'name' => $this->faker->unique()->randomElement(self::$medicalDepartmentNames),

        //     // يولد نصًا أكثر واقعية كـ"وصف"
        //     'description' => $this->faker->realText(200),

        //     // يولد رابط صورة عشوائي بناءً على الكلمات الرئيسية
        // ];
        $data = self::$departments[self::$index];

        self::$index++;

        return [
            'name' => $data['name'],
            'description' => $data['description'],
            'image' => 'storage/departments_image/D' . self::$index . '.jpg',
        ];
    }
}
