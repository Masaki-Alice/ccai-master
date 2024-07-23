import sys
import json
import base64
import vertexai
from vertexai.generative_models import (
    GenerativeModel,
    Part,
    GenerationConfig,
    HarmCategory,
    HarmBlockThreshold,
    SafetySetting,
)

PAYLOAD = json.loads(base64.b64decode(sys.argv[1]))
project = "ccai-dev-project"
location = "europe-west3"

PROMPT = f"""
    PERSONA:
    You are a world class language specialist.
    
    TASK:
    You have been provided with the following conversation in JON format delimited with <conversation> and </conversation>:
    <conversation>
    {PAYLOAD}
    </conversation>
    Read thru each line of the conversation and make sure you understand the context correctly.
    In one paragraph, generate a concise summarization of the whole conversation.
    
""".strip()

## PROMPT RE-READ STRATEGY
PROMPT = f"""
{PROMPT}
Read the task again.
{PROMPT}
"""


# Initialize Vertex AI
vertexai.init(project=project, location=location)

generation_config = GenerationConfig(max_output_tokens=8000)
model = GenerativeModel(model_name="gemini-1.5-pro-preview-0514")
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

# Build prompt scenario
prompt = PROMPT

# Execute inference
response = model.generate_content(
    [prompt],
    generation_config=generation_config,
    safety_settings=safety_config,
)

# Embed model metadata in JSON response
response = response.text.replace("```json", "").replace("```", "")
print(response)
