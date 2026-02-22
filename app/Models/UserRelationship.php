<?php

namespace App\Models;

use App\Enums\CommunicationChannel;
use App\Enums\RelationshipType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRelationship extends Pivot
{
    protected $table = 'user_relationships';

    protected $casts = [
        'is_emergency_contact' => 'boolean',
        'is_billing_contact' => 'boolean',
        'relationship_type' => RelationshipType::class,
        'preferred_communication_channel' => CommunicationChannel::class,
    ];
}
