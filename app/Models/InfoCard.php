<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $position
 * @property string $icon
 * @property string $title
 * @property string|null $subtitle
 */
#[Fillable(['position', 'icon', 'title', 'subtitle'])]
class InfoCard extends Model
{
    //
}
