<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class User extends Authenticatable implements Auditable
{

    use AuthenticationLoggable;
    use HasApiTokens;
    use HasDynamicAttributes;
    use HasFactory;
    use HasPermissions;
    use HasRoles;
    use Notifiable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;
    use UuidForKey;

    protected array $dynamicKeys = ['bot_account', 'bank_name', 'spam'];

    public function generateTags(): array
    {
        return [
            'admin, user',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'category',
        'timezone',
        'dse_account',
    ];

    protected $guarded = ['type'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'role',
        //        'category',
        //        'custodians',
        'jame',
        'role_id',
    ];

    public function getFlexAccNoAttribute($value): string
    {
        return trim($value);
    }

    public function getCustodiansAttribute($key): \Illuminate\Support\Collection
    {
        return \DB::table('customer_custodians')
            ->where('user_id', $this->getAttribute('id'))->get();
    }

    public function getJameAttribute(): string
    {
        if ($this->getAttribute('type') == 'joint') {
            $profile = \DB::table('profiles')->where('user_id', $this->getAttribute('id'))->first();
            $profileJ = \DB::table('joint_profiles')->where('user_id', $this->getAttribute('id'))->first();

            return strtoupper($profile->firstname.' '.$profile->middlename.' '.$profile->lastname.' & '.$profileJ->firstname.' '.$profileJ->middlename.' '.$profileJ->lastname);
        } else {
            return '';
        }
    }

    public function getCategoryAttribute()
    {
        return \DB::table('customer_categories')->find($this->getAttribute('category_id'));
    }

    public function getRoleIdAttribute()
    {
        $status = \DB::table('model_has_roles')->where('model_id', $this->getAttribute('category_id'))->first();
        if (! empty($status)) {
            return $status->role_id;
        }

        return '';
    }

    public function getRoleAttribute()
    {
        $roles = $this->getRoleNames();
        if (! empty($roles)) {
            if (is_array($roles)) {
                return $roles[0];
            } else {
                return $roles;
            }
        }

        return '';
    }

    public function scopeCustomers()
    {
        return $this->whereIn('type', ['individual', 'corporate', 'joint', 'minor']);
    }

    public function scopePayees()
    {
        return $this->whereIn('type', ['payee']);
    }

    public function scopeAdmins()
    {
        return $this->whereNull('type')->where('is_admin', true);
    }

    public function scopeActive()
    {
        return $this->where('status', 'active');
    }

    /**
     * Get the phone associated with the user.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}
