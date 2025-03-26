<?php

namespace App\Models;

use App\Models\Email\EmailMessage;
use App\Models\Payment\Payment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;
use App\Traits\GracePeriodTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles,SoftDeletes;
    use HasSubscriptions;
    use GracePeriodTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'username',
        'image_url',
        'company',
        'country',
        'whatsapp',
        'active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the user's unsubscribe information.
     */
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }


    public function emails(){
        return $this->hasMany(EmailList::class);
    }

    public function emailMessages()
    {
        return $this->hasMany(EmailMessage::class);
    }

    public function list()
    {
        return $this->belongsTo(EmailListName::class, 'list_id');
    }

    public function forceSetConsumption($featureName, $amount)
    {
        $feature = \LucasDotVin\Soulbscription\Models\Feature::where('name', $featureName)->first();

        if (!$feature) {
            throw new \Exception("Feature not found: {$featureName}");
        }

        return \LucasDotVin\Soulbscription\Models\FeatureConsumption::updateOrCreate(
            [
                'subscriber_type' => get_class($this),
                'subscriber_id' => $this->id,
                'feature_id' => $feature->id
            ],
            [
                'consumption' => (float) $amount,
                'expired_at' => null,
                'updated_at' => now()
            ]
        );
    }


    public function servers()
    {
        return $this->hasMany(Server::class, 'assigned_to_user_id');
    }

}
