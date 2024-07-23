<?php
namespace  App\Library;

use App\Models\Inventory;
use App\Models\AudioSlice;

class Transcription
{
    static function findAudio($audioID, $audioType)
    {
        $audio = null;
        switch ($audioType) {
            case 'inventory':
                $audio = Inventory::find($audioID);
                break;
            case 'slice':
                $audio = AudioSlice::find($audioID);
                break;
        }

        return $audio;
    }
}