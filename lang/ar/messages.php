<?php

return [
    // ######## DOCTOR ######## //



    'doctor_not_found' => 'الطبيب غير موجود',
    'user_not_found' => 'المستخدم غير موجود',
    'doctor_profile_not_found' => 'الملف الشخصي للطبيب غير موجود',

    'article_created_successfully' => 'تم إنشاء المقال بنجاح',
    'article_created_failed' => 'حدث خطأ ما، لم يتم إنشاء المقال',
    'article_updated_successfully' => 'تم تحديث المقال بنجاح',
    'article_updated_failed' => 'فشل تحديث المقال',
    'article_deleted_successfully' => 'تم حذف المقال بنجاح',
    'article_deleted_failed' => 'فشل حذف المقال',
    'article_not_found' => 'المقال غير موجود',
    'articles_returned_successfully' => 'تم جلب المقالات بنجاح',
    'article_returned_successfully' => 'تم جلب المقال بنجاح',

    'profile_updated_successfully' => 'تم تحديث الملف الشخصي بنجاح',

    'appointments_returned_successfully' => 'تم جلب المواعيد بنجاح',
    'appointments_not_found' => 'لا توجد مواعيد',

    'preview_added_successfully' => 'تمت إضافة المعاينة بنجاح',
    'preview_added_failed' => 'فشلت إضافة المعاينة',
    'preview_updated_successfully' => 'تم تحديث المعاينة بنجاح',
    'preview_updated_failed' => 'فشل تحديث المعاينة',
    'preview_deleted_successfully' => 'تم حذف المعاينة بنجاح',
    'preview_deleted_failed' => 'فشل حذف المعاينة',
    'preview_not_found' => 'المعاينة غير موجودة',
    'previews_returned_successfully' => 'تم جلب المعاينات بنجاح',
    'preview_returned_successfully' => 'تم جلب المعاينة بنجاح',

    'patients_returned_successfully' => 'تم جلب المرضى بنجاح',
    'no_doctors_found' => 'لم يتم العثور على أي أطباء',
    'no_preview_patients' => 'لا يوجد مرضى لديهم معاينات لهذا الطبيب',

    'search_input_required' => 'حقل البحث مطلوب',
    'found_successfully' => 'تم العثور عليه بنجاح',
    'found_failed' => 'فشل البحث',

    'active_patient_info_returned_successfully' => 'تم جلب معلومات المريض النشط بنجاح',
    'patient_not_found' => 'المريض غير موجود',

    'unknown' => 'غير معروف',

    // ######## Admin ######## //

    // Secretary
    'secretary_created_successfully' => 'تم إنشاء السكرتير بنجاح.',
    'secretary_updated_successfully' => 'تم تحديث بيانات السكرتير بنجاح.',
    'secretary_deleted_successfully' => 'تم حذف السكرتير بنجاح.',
    'secretary_already_exists' => 'السكرتير موجود بالفعل.',
    'no_secretary_account_to_delete' => 'لا يوجد حساب سكرتير لحذفه.',

    // Doctor
    'doctor_created_successfully' => 'تم إنشاء الطبيب بنجاح.',
    'doctor_updated_successfully' => 'تم تحديث بيانات الطبيب بنجاح.',
    'doctor_deleted_successfully' => 'تم حذف الطبيب بنجاح.',

    // Department
    'department_created_successfully' => 'تم إنشاء القسم بنجاح.',
    'department_deleted_successfully' => 'تم حذف القسم بنجاح.',
    'department_not_found' => 'القسم غير موجود.',

    // User
    'user_deleted_successfully' => 'تم حذف المستخدم بنجاح.',

    // General errors
    'validation_failed' => 'فشل التحقق من البيانات.',
    'server_error' => 'خطأ في الخادم: :error',
    'unknown_error' => 'حدث خطأ غير معروف.',
    'language_required' => 'يجب إدخال نوع اللغة.',

    'confirmation_email_sent' => 'تم إرسال رسالة التأكيد بنجاح!',
    'invalid_or_expired_token' => 'الرمز غير صالح أو منتهي الصلاحية.',
    'reset_password_confirmation' => 'هل أنت متأكد أنك تريد إعادة تعيين كلمة المرور؟',
    'account_not_found' => 'ليس لديك حساب.',
    'password_reset_successfully' => 'تم إعادة تعيين كلمة المرور بنجاح.',
    'logged_out_successfully' => 'تم تسجيل الخروج بنجاح.',
    'register_before' => 'سجل أولاً.',
    'verification_code_expired' => 'انتهت المهلة! عذراً، هذا الرمز لا يعمل. يرجى التسجيل مرة أخرى.',
    'verification_code_correct' => 'شكرًا لك، لقد أدخلت رمزًا صحيحًا.',
    'verification_code_incorrect' => 'عذراً، لقد أدخلت رمزًا غير صحيح.',
    'verification_code_resent' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني بنجاح.',
    'account_verification_required' => 'يجب عليك التحقق من حسابك قبل ذلك.',
    'no_secretary_account_for_delete' => 'لا يوجد حساب سكرتير لحذفه.',

    'appointment_added_successfully' => 'تمت إضافة الموعد بنجاح.',
    'appointment_not_found' => 'الموعد غير موجود.',
    'appointment_already_accepted' => 'لقد قبلت هذا الموعد بالفعل.',
    'appointment_conflict' => 'لا يمكن قبول هذا الموعد. يوجد موعد مقبول في نفس الوقت.',
    'appointment_accepted_successfully' => 'تم قبول الموعد بنجاح.',
    'appointment_rejected_successfully' => 'تم رفض الموعد بنجاح.',
    'appointments_found' => 'تم العثور على المواعيد.',
    'no_monthly_leaves' => 'لا توجد إجازات شهرية.',
    'all_monthly_leaves' => 'هذه كل الإجازات الشهرية.',
    'search_query_required' => 'حقل البحث مطلوب.',
    'no_appointments_found' => 'لا توجد مواعيد.',
    'no_accepted_appointments_found' => 'لا توجد مواعيد مقبولة.',
    'release_rates_processed_successfully' => 'تمت معالجة الرسوم بنجاح.',
    'day_not_found' => 'اليوم غير موجود.',
    'no_appointments_yet' => 'لا توجد مواعيد حتى الآن.',
    'secretary_info_retrieved_successfully' => 'تم استرجاع معلومات السكرتير بنجاح.',
    'no_doctors_yet' => 'لا توجد أطباء حتى الآن.',
    'doctors_retrieved_successfully' => 'تم جلب الأطباء بنجاح.',
    'doctor_info_returned' => 'هذه معلومات الطبيب.',
    'doctors_returned' => 'هذه كل الأطباء.',
    'all_departments_returned' => 'هذه كل الأقسام.',
    'no_departments_yet' => 'لا توجد أقسام حتى الآن.',
    'department_returned' => 'هذا القسم.',
    'no_department_found' => 'لا يوجد قسم.',
    'no_leaves_found_for_doctor' => 'لا توجد إجازات لهذا الطبيب.',
    'no_results_found' => 'لا توجد نتائج.',
    'day_not_found_general' => 'هذا اليوم غير موجود.',
    'departments_with_doctors_of_day_returned' => 'هذه الأقسام مع أطباء هذا اليوم.',
    'image_uploaded_successfully' => 'تم رفع الصورة بنجاح.',
    'no_file_to_upload' => 'لا توجد ملفات لرفعها.',
    'unauthorized' => 'غير مصرح لك.',
    'patient_not_found_by_id' => 'لم يتم العثور على المريض. الرقم الذي أدخلته غير صحيح.',
    'all_appointments_returned' => 'هذه كل المواعيد.',
    'missing_doctor_or_day_id' => 'missing doctor_id or day_id.',
    'day_not_found_with_id' => 'اليوم غير موجود (المعرف: :id).',
    'doctor_not_found_with_id' => 'الطبيب غير موجود (المعرف: :id).',
    'doctor_day_conflict' => 'لا يمكن للطبيب :doctor أخذ اليوم :day لأن هذا اليوم تم حجزه من قبل طبيب آخر في نفس القسم.',
    'leave_creation_summary' => ':created إجازة تم إنشاؤها، :errors أخطاء.',
    'no_doctors_found_general' => 'لم يتم العثور على أي أطباء.',
    'no_departments_found' => 'لا توجد أقسام.',
    'enter_patient_already_active' => 'يوجد مريض بالفعل داخل العيادة.',
    'enter_patient_successful' => 'تم دخول المريض بنجاح.',


];