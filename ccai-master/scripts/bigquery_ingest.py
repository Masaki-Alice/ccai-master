# Set this to current python environment
#! /opt/conda/bin/python

from google.cloud import bigquery
import json
import sys
import base64
from datetime import datetime, timezone
import re
import uuid

# Extract payload from commandline arguments
payload = json.loads(base64.b64decode(sys.argv[1]))
table = sys.argv[2]

# Convert a time string in format %H:%M:%S.%f to microseconds since start of the day.
def time_to_usec(time_str):
    time_obj = datetime.strptime(time_str, "%H:%M:%S.%f")
    midnight = time_obj.replace(hour=0, minute=0, second=0, microsecond=0)
    delta = time_obj - midnight
    return int(delta.total_seconds() * 10**6)

# Set timestamps and date details
start_timestamp_utc = time_to_usec(payload["transcription"]["transcript"][0]["start"])
load_timestamp_utc = int(datetime.now(timezone.utc).timestamp())
load_datetime = datetime.fromtimestamp(load_timestamp_utc, tz=timezone.utc)

# Function to convert time string to seconds
def time_to_seconds(time_str):
    h, m, s = map(float, time_str.split(":"))
    return h * 3600 + m * 60 + s

# Calculate total speaking time for AGENT and CUSTOMER
total_time = 0
agent_time = 0
customer_time = 0

for entry in payload["transcription"]["transcript"]:
    start_time = time_to_seconds(entry["start"])
    end_time = time_to_seconds(entry["end"])
    duration = end_time - start_time
    total_time += duration

    if entry["speaker"] == "AGENT":
        agent_time += duration
    elif entry["speaker"] == "CUSTOMER":
        customer_time += duration

agent_percentage = (agent_time / total_time) * 100
customer_percentage = (customer_time / total_time) * 100

# get conversation topics
topics = json.loads(payload["topics"])

# Regular expression to extract conversationName
pattern = r"\[.*?\]_.*?_\d+"

match = re.search(pattern, payload["disk_path"])

if match:
    conversationName = match.group(0)
else:
    conversationName = payload["id"]

# Regular expression to extract AgentID
pattern = r"\[([a-zA-Z]+),\s([a-zA-Z]+)\]_(\d+)-"

match = re.search(pattern, payload["disk_path"])

if match:
    name = match.group(1) + " " + match.group(2)
    id_number = match.group(3)
    agentId = f"{name}_{id_number}"
else:
    agentId = payload["id"]

#Convert sentiment scores to -1.0 - 1.0 scale
def convert_score(old_score, old_min=0, old_max=100, new_min=-1.0, new_max=1.0):
    new_score = ((old_score - old_min) / (old_max - old_min)) * (new_max - new_min) + new_min
    return new_score

#Calculate automated quality evaluation score
def calculate_quality_evaluation_score():
    score = {
        "empathy_quality_score": 0,
        "audibility_quality_score": 0,
        "first_call_resolution_quality_score": 0,
        "vetting_quality_score": 0,
        "personalization_quality_score": 0
    }

    if payload["sentiment_analysis"]["agent_only"]["has_empathy"]:
        score["empathy_quality_score"] = 15

    if payload["sentiment_analysis"]["agent_only"]["has_audibility_issues"]:
        score["audibility_quality_score"] = 10

    if payload["sentiment_analysis"]["agent_only"]["first_call_resolution"]:
        score["first_call_resolution_quality_score"] = 40

    if payload["sentiment_analysis"]["agent_only"]["has_vetting"]:
        score["vetting_quality_score"] = 20

    if payload["sentiment_analysis"]["agent_only"]["has_personalization"]:
        score["personalization_quality_score"] = 5
    
    # @todo callback adherence values need to be added. Check back later
    # if payload["sentiment_analysis"]["agent_only"]["callback_promise"]:
    #     score["callback_promise_quality_score"] = 20

    quality_evaluation_score = sum(score.values())

    return quality_evaluation_score

quality_evaluation_score = calculate_quality_evaluation_score()

#Generate custome highlights
def update_schema_based_on_payload():
    # Initialize the schema
    schema = {
        "sentences": [
            {
                "highlightData": []
            }
        ]
    }
    
    # Check for empathy
    if payload["sentiment_analysis"]["agent_only"]["has_empathy"]:
        schema["sentences"][0]["highlightData"].append({
            "displayName": "Use of empathy statements",
            "type": "CUSTOM"
        })
        
     # Check personalisation
    if payload["sentiment_analysis"]["agent_only"]["has_personalization"]:
        schema["sentences"][0]["highlightData"].append({
            "displayName": "Has personalization",
            "type": "CUSTOM"
        })
        
    # Check for knowledge gaps
    if payload["sentiment_analysis"]["agent_only"]["has_knowledge_gaps"]:
        schema["sentences"][0]["highlightData"].append({
            "displayName": "Has knowledge gaps",
            "type": "CUSTOM"
        })

    # Check for audibility issues 
    if payload["sentiment_analysis"]["complete"]["has_audibility_issues"]:
        schema["sentences"][0]["highlightData"].append({
            "displayName": "Has audibility issues",
            "type": "CUSTOM"
        })
    
    # Check for first call resolution
    if payload["sentiment_analysis"]["customer_only"]["first_call_resolution"]:
        schema["sentences"][0]["highlightData"].append({
            "displayName": "First call resolution",
            "type": "CUSTOM"
        })
        
    # Check for silence
    if payload["sentiment_analysis"]["agent_only"]["has_silence"]:
        schema["sentences"][0]["highlightData"].append({
            "displayName": "Has silence",
            "type": "CUSTOM"
        })

    return schema

sentences = update_schema_based_on_payload()

record = {
    "id": str(uuid.uuid4()),
    "conversationName": conversationName,
    "audioFileUri": payload["disk_path"],
    "agentId": agentId,
    "startTimestampUtc": start_timestamp_utc,
    "loadTimestampUtc": load_timestamp_utc,
    "analysisTimestampUtc": load_timestamp_utc,
    "year": load_datetime.year,
    "month": load_datetime.month,
    "day": load_datetime.day,
    "durationNanos": int(payload["transcription"]["metadata"]["playback_length"]),
    "silenceNanos": int(payload["transcription"]["metadata"]["silence_duration"]),
    "silencePercentage": float(payload["transcription"]["metadata"]["silence_percentage"].replace("%", "")),
    "conversationSentimentScore": float(convert_score(payload["sentiment_analysis"]["complete"]["sentiment_score"])),
    "conversationSentimentMagnitude": float(convert_score(payload["sentiment_analysis"]["complete"]["sentiment_magnitude"])),
    "automatedQualityEvaluationScore": quality_evaluation_score,
    "conversationSummary": payload["summary"],
    "agentSpeakingPercentage": float(round(agent_percentage, 2)),
    "clientSpeakingPercentage": float(round(customer_percentage, 2)),
    "agentSentimentScore": float(convert_score(payload["sentiment_analysis"]["agent_only"]["sentiment_score"])),
    "agentSentimentMagnitude": float(payload["sentiment_analysis"]["agent_only"]["sentiment_magnitude"]),
    "clientSentimentScore": float(convert_score(payload["sentiment_analysis"]["customer_only"]["sentiment_score"])),
    "clientSentimentMagnitude": float(payload["sentiment_analysis"]["customer_only"]["sentiment_magnitude"]),
    "transcript": payload["text"],
    "turnCount": len(payload["transcription"]["transcript"]),
    "languageCode": payload["transcription"]["metadata"]["language"],
    "medium": "CHAT",
    "issues": [{"name": topic} for topic in topics],
    # "entities": [
    #     {
    #         "type": payload["sentiment_analysis"],
    #         "name": payload["sentiment_analysis"]["sentiment"],
    #         }
    #     ],
    "sentences": sentences["sentences"],
    "latestSummary": {"text": payload["summary"]},
}

# Insert data into bigquery
client = bigquery.Client()
errors = client.insert_rows_json(table, [record])

# Prepare response
if errors:
    response = {
        "status": "Failed",
        "bigquery_id": record["id"],
        "error": errors,
    }
else:
    response = {"status": "OK", "bigquery_id": record["id"]}

# Return JSON response to caller
print(json.dumps(response))
