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
    You are an expert language analyst who is an expert in at extracting high-level topics 
    from recorded call centre conversations.
    
    TASK:
    Your task is to identify high-level topics of a single conversation that is 
    formatted in JSON and delimited by <conversation> and </conversation> tags below.
    <conversation>
        {PAYLOAD}
    </conversation>
    Avoid giving specific details. 
    Instead, provide only unique topics found in the conversation as a JSON array.
    Provide your output in JSON format compliant with ECMA-404 standard
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
