# Set this to current python environment
#! /opt/conda/bin/python

import vertexai
from vertexai.generative_models import (
    GenerativeModel,
    Part,
    GenerationConfig,
    HarmCategory,
    HarmBlockThreshold,
    SafetySetting,
)
import sys
import json

# Bucket link supplied as argument
BUCKET_LINK = sys.argv[1]

project = "ccai-dev-project"
location = "europe-west3"

# Initialize Vertex AI
vertexai.init(project=project, location=location)

safety_config = [
    SafetySetting(
        category=HarmCategory.HARM_CATEGORY_DANGEROUS_CONTENT,
        threshold=HarmBlockThreshold.BLOCK_ONLY_HIGH,
    ),
    SafetySetting(
        category=HarmCategory.HARM_CATEGORY_HARASSMENT,
        threshold=HarmBlockThreshold.BLOCK_ONLY_HIGH,
    ),
    SafetySetting(
        category=HarmCategory.HARM_CATEGORY_HATE_SPEECH,
        threshold=HarmBlockThreshold.BLOCK_ONLY_HIGH,
    ),
    SafetySetting(
        category=HarmCategory.HARM_CATEGORY_SEXUALLY_EXPLICIT,
        threshold=HarmBlockThreshold.BLOCK_ONLY_HIGH,
    ),
]

generation_config = GenerationConfig(max_output_tokens=8000)

model = GenerativeModel(model_name="gemini-1.5-pro-preview-0514")

# Build prompt scenario
prompt = """
    You are a world class transcription professional.
    Transcribe the provided audio file into the following custom format:
    {
        "metadata": { 
            "language": "en", 
            "probability": "75%",
            "num_speakers": 1,
            "silence_percentage": "Silence percent here",
            "playback_length": "Playback length here",
            "silence_duration": "Playback length here",
        },
        "transcript": [
            {
            "start": 0,
            "end": 12.8,
            "text": "Transcribed text here",
            "speaker": "The active speaker here",
            }
        ]
    }
    Use the following rules when compiling the transcription:
    - The silence_percentage field should contain the total percentage of silence detected in the audio file
    - The speaker field should contain the active speaker in the audio file. Name the first speaker AGENT, second speaker CUSTOMER and so on
    - The metadata.language field should contain the dominant language detected language
    - The probability field should be an accurate measure of how much of the dominant language was found in the audio
    - The num_speakers field should contain the total number of speakers detected in the audio file
    - The start field should be the timecode of the beginning of the transcribed text in the format "00:00:00.000"
    - The end field should be the timecode of the end of the transcribed text  in the format "00:00:00.000"
    - The playback_length field should be the length of the audio file in nanoseconds
    - The silence_duration field should be the length of silence in the audio file in nanoseconds
    - Ensure the JSON format you produce is fully compliant with ECMA-404 standard
"""

# Execute inference
response = model.generate_content(
    [Part.from_uri(BUCKET_LINK, mime_type="audio/mp3"), prompt],
    generation_config=generation_config,
    safety_settings=safety_config,
)

# Collect extra model metadata
raw_response = response.to_dict()
safety_profile = raw_response["candidates"][0]["safety_ratings"]
finish_reason = raw_response["candidates"][0]["finish_reason"]
usage_metadata = raw_response["usage_metadata"]

# Embed model metadata in JSON response
# print(response.text)
response = response.text.replace("```json", "").replace("```", "")
response = json.loads(response)
response["metadata"]["safety_profile"] = safety_profile
response["metadata"]["api_usage"] = usage_metadata

# Return JSON output to caller
print(json.dumps(response, indent=4))
