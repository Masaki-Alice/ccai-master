# Set this to current python environment
#! /opt/conda/bin/python

import torch
from faster_whisper import WhisperModel
import os
import multiprocessing
import json
import sys


FILE = sys.argv[1]
model_size = "large-v3"
device = "cuda"

transcript = []
model = WhisperModel(model_size, device=device, compute_type="float16")
segments, info = model.transcribe(
    FILE,
    beam_size=10,
    without_timestamps=False,
    vad_filter=True,
)

for segment in segments:
    transcript.append(
        {"start": segment.start, "end": segment.end, "text": segment.text}
    )

# Build transcript

output = {
    "metadata": {
        "language": info.language,
        "probability": f"{round(info.language_probability*100)}%",
    },
    "transcript": transcript,
}

print(json.dumps(output, indent=4))
