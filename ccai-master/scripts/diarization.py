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

generation_config = GenerationConfig(max_output_tokens=8000, temperature=1, top_p=0.95)

model = GenerativeModel(model_name="gemini-1.5-pro-001")

# Build prompt scenario
prompt = """
    <context>
    You are an english and swahili audio voice recognition, diarization and text to audio alignment system.
    </context>
    <instructions>
    Carefully Identify the speakers as speaker A or speaker B or Speaker C etc.
    Keep the original words spoken and do not attempt to translate them.
    Detect and transcribe any non verbal sounds like laughter, coughing, etc.
    Do not change the order of the words spoken.
    Do not truncate the words spoken nor add any words.
    Identify the start and end time in minutes:seconds format for each speaker's conversation turn in the audio clip.
    Output the diarization in the json format:[{ "speaker": "", "start":"" , "end":"" , "words":""}]
    Ensure the JSON format you produce is fully compliant with ECMA-404 standard
    The first speaker should always be labelled AGENT and the second speaker should always be labelled CUSTOMER
    </instructions>

    <Example>
    [
        {
            "speaker": "AGENT",
            "start": "00:00",
            "end": "10:10",
            "words": "I don't know if the expectation I don't know if the expectation was for me."
        },
        {
            "speaker": "CUSTOMER",
            "start": "10:00",
            "end": "13:00",
            "words": "Yeah Yeah"
        }
    ]
    </Example>
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

# Return JSON output to caller
print(json.dumps(response, indent=4))
