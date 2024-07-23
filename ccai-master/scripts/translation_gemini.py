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
import base64

JSON_FORMAT = """
export interface Root {
  metadata: Metadata
  transcript: Transcript[]
}
export interface Transcript {
  start: string
  end: string
  text: string
  speaker: string
}
"""

JSON_INPUT = json.loads(base64.b64decode(sys.argv[1]))
# JSON_INPUT = json.loads(base64.b64decode(JSON_INPUT))

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
prompt = f"""
    You are a world class translation professional that returns valid JSON output.
    You have received some data in JSON format represented by the TypeScript interfaces below:

    {JSON_FORMAT}

    The data you have received is in the followinf JSON format:

    {JSON_INPUT}

    You are tasked with translating the JSON input while following the rules below:
    - In the 'transcript' array, translate the field named 'text' to English language
    - Your output can only be in valid JSON format
    - Translate all the items in the 'transcript' array
    - Use JSON standard ECMA-404
"""

# Execute inference
response = model.generate_content(
    [prompt],
    generation_config=generation_config,
    safety_settings=safety_config,
)

# Embed model metadata in JSON response
response = response.text.replace("```json", "").replace("```", "")
response = json.loads(response)

# # Return JSON output to caller
print(json.dumps(response, indent=4))
