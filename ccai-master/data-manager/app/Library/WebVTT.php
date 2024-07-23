<?php

namespace App\Library;

class WebVTT
{
    static function generate_from_json($jsonTranscript)
    {
        $output = "WEBVTT\n\n";
        foreach ($jsonTranscript as $i => $entry) {
            // $start_time = gmdate("H:i:s", $entry['start']);
            // $end_time = gmdate("H:i:s", $entry['end']);
            // $text = trim($entry['text']);
            // $output .= ($i + 1) . "\n";
            // $output .= "$start_time --> $end_time\n";
            // $output .= "$text\n\n";
        }

        return trim($output);
    }
}
