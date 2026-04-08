<?php

return [

    // Navigation
    'resource_label' => 'تسلسل الترقيم',
    'resource_plural' => 'تسلسلات الترقيم',

    // Reset frequency
    'reset_frequency' => [
        'never' => 'أبداً',
        'yearly' => 'سنوي',
        'monthly' => 'شهري',
        'daily' => 'يومي',
    ],

    // Form
    'form' => [
        'sequence_details' => 'تفاصيل التسلسل',
        'name' => 'الاسم',
        'model_type' => 'النموذج',
        'attribute' => 'الحقل',
        'attribute_helper' => 'حقل النموذج المراد تعبئته تلقائياً (مثال: invoice_number)',
        'pattern' => 'النمط',
        'pattern_helper' => 'الرموز: {sequence:4}، {year}، {year:2}، {month}، {day}، {prefix}، {suffix}، {attribute:name}',
        'formatting' => 'التنسيق',
        'prefix' => 'البادئة',
        'suffix' => 'اللاحقة',
        'initial_value' => 'القيمة الابتدائية',
        'reset_settings' => 'إعدادات إعادة التعيين',
        'reset_frequency' => 'تكرار إعادة التعيين',
        'fiscal_year_start_month' => 'شهر بداية السنة المالية',
        'is_gap_free' => 'وضع بدون فجوات',
        'is_gap_free_helper' => 'يستخدم قفل قاعدة البيانات لضمان عدم وجود فجوات في أرقام التسلسل',
        'is_active' => 'نشط',
        'custom_tokens' => 'رموز مخصصة',
        'token_name' => 'اسم الرمز',
        'token_resolver' => 'المحلل',
        'custom_tokens_helper' => 'ربط أسماء الرموز المخصصة بالمحللات (مثال: branch => attribute:branch.code)',
    ],

    // Table
    'table' => [
        'name' => 'الاسم',
        'model_type' => 'النموذج',
        'pattern' => 'النمط',
        'reset_frequency' => 'إعادة التعيين',
        'is_gap_free' => 'بدون فجوات',
        'is_active' => 'نشط',
        'created_at' => 'تاريخ الإنشاء',
    ],

];
