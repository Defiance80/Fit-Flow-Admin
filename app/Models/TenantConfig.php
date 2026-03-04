<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantConfig extends Model
{
    protected $fillable = [
        'facility_id', 'app_name', 'domain', 'subdomain', 'logo_url', 'favicon_url',
        'primary_color', 'secondary_color', 'accent_color', 'theme_config',
        'support_email', 'support_phone', 'custom_css', 'feature_flags',
    ];

    protected $casts = [
        'theme_config' => 'array',
        'feature_flags' => 'array',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }
}
