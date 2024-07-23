<?php

namespace App\Models;

use App\Library\WebVTT;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at'];

    protected $table = 'data_inventory';

    protected $casts = [
        'transcription' => 'array',
        'dlp_risk_analysis' => 'array',
        'gcs_metadata' => 'array',
        'translation' => 'array',
        'sentiment_analysis' => 'array',
    ];

    protected $appends = [
        'vtt',
        'vtt_html',
        'text'
    ];

    function getVttAttribute()
    {

        return @WebVTT::generate_from_json($this->transcription['transcript']);
    }

    function getVttHtmlAttribute()
    {
        return nl2br(@WebVTT::generate_from_json($this->transcription['transcript']));
    }

    function getRedactedTranscriptAttribute($val)
    {
        return nl2br($val);
    }

    function slices()
    {
        return $this->hasMany(AudioSlice::class, 'inventory_id', 'id');
    }

    function getTextAttribute()
    {
        $transcript = @collect($this->transcription['transcript']);

        if ($transcript) {
            $transcript = $transcript->pluck('text');
            return trim(implode('. ', $transcript->toArray()));
        } else {
            return null;
        }
    }
}
