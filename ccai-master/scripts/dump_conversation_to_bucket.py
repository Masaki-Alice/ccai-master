import json
from google.cloud import storage
import base64
import sys
from datetime import datetime, timezone

# Extract payload from commandline arguments
transcript = json.loads(base64.b64decode(sys.argv[1]))
id = sys.argv[2]
bucket_name = "transcripts-gcs"


def time_to_usec(time_str):
    time_obj = datetime.strptime(time_str, "%H:%M:%S.%f")
    midnight = time_obj.replace(hour=0, minute=0, second=0, microsecond=0)
    delta = time_obj - midnight
    return int(delta.total_seconds() * 10**6)


def convert_transcript_to_conversation(transcript):
    entries = []

    for idx, turn in enumerate(transcript):
        # Calculate the start time in microseconds since the start of the day
        start_usec = time_to_usec(turn["start"])

        # Assign roles and user_ids based on turn index (1-based)
        if (idx + 1) % 2 != 0:  # Odd index (1, 3, 5, ...)
            role = "AGENT"
            user_id = 1
        else:  # Even index (2, 4, 6, ...)
            role = "CUSTOMER"
            user_id = 2

        # Create the entry for each turn
        entry = {
            "start_timestamp_usec": start_usec,
            "text": turn["text"],
            "role": role,
            "user_id": user_id,
        }
        entries.append(entry)

    conversation = {"entries": entries}
    return conversation


# Convert transcript to conversation format
conversation = convert_transcript_to_conversation(transcript)
json_data = json.dumps(conversation, indent=4)

storage_client = storage.Client()
bucket = storage_client.bucket(bucket_name)
blob = bucket.blob(f"conversation_{id}.json")
blob.upload_from_string(json_data, content_type="application/json")

print(json.dumps({"conversation": conversation}, indent=4))
