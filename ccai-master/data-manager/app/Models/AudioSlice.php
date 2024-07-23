<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Library\WebVTT;

class AudioSlice extends Model
{
    use HasFactory;

    protected $table = 'audio_slices';

    protected $guarded = ['id', 'created_at'];

    protected $casts = [
        'transcription' => 'array',
        'dlp_risk_analysis' => 'array',
    ];

    protected $appends = [
        'vtt',
        'vtt_html',
        'html',
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

    function getHtmlAttribute()
    {
        $transcript = null;
        if ($this->transcription) {
            $transcript = collect($this->transcription['transcript'])->pluck('text')->toArray();
            $transcript = trim(implode('. ', $transcript));
        }

        return $transcript;
    }
}
