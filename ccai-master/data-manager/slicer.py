# Set this to current python environment
#! /opt/conda/bin/python
import os
import sys
import json
from pydub import AudioSegment

"""
The calling process must provide the following arguments without trailing slashes:
FILE_PATH: absolute path to the audio source file and the
DESTINATION_FOLDER: absolute path to the destination folder where the pieces will be stored
example:
python segment.py "[SOURCE_FILE_PATH]" "[DESTINATION_FOLDER]"
python segments.py "/var/www/ccai/data-manager/public/audio/23783662_kijana-eric-nyairo-wa-nyamira-atengeneza-ndege.webm.mp3" "/var/www/ccai/data-manager/storage/app/swanglish"
"""
FILE_PATH = sys.argv[1]
DESTINATION_FOLDER = sys.argv[2]

# 30 second splits as recommended for ML audio datasets
SPLIT_LENGTH = 30 * 1000
SOURCE_FILE = AudioSegment.from_mp3(FILE_PATH)
NUM_SPLITS = len(SOURCE_FILE) // SPLIT_LENGTH

# Extract file name without extensions
FILE_NAME = (
    os.path.basename(FILE_PATH).split("/")[-1].replace(".webm", "").replace(".mp3", "")
)

"""
We want to apply a streaming quality bitrate to the pieces
MP3 bitrate range is between 100k - 170k (audio streaming platforms e.g. Spotify, Apple Music, etc)
"""
bitrate = "140k"  # Audio quality

SEGMENTS = []
for i in range(NUM_SPLITS):
    start_time = i * SPLIT_LENGTH
    end_time = (i + 1) * SPLIT_LENGTH

    OUTPUT_FILE = f"{DESTINATION_FOLDER}/{FILE_NAME}_seg{i}.mp3"

    audio_chunk = SOURCE_FILE[start_time:end_time]
    audio_chunk.export(OUTPUT_FILE, format="mp3", bitrate=bitrate)

    SEGMENTS.append(OUTPUT_FILE)

# Collect extra metrics about the audio file
extra_metrics = {
    "loudness": SOURCE_FILE.dBFS,
    "channels": SOURCE_FILE.channels,
    "sample_width": SOURCE_FILE.sample_width,
    "frame_rate": SOURCE_FILE.frame_rate,
    "length_in_seconds": SOURCE_FILE.duration_seconds,
    "num_splits": NUM_SPLITS,
    "segments": SEGMENTS,
}

print(json.dumps(extra_metrics, indent=4))
