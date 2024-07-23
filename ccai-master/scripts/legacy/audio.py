import os
import pandas as pd
from pytube import YouTube
import ffmpeg
import sys
# from lib.common import dump
# from lib.common import write_file
import soundfile as sf
import uuid
import librosa
from concurrent.futures import ProcessPoolExecutor, as_completed
from slugify import slugify
YouTube.unicode = str

def make_audio_destination_folder(path="tmp/audio_destination"):
    if not os.path.exists(path):
        print("OK => Creating destination_folder")
        os.makedirs(path)
        print(f"Ok => Created {path}")


def download_audio_from_youtube(video_url, output_dir):
    """
    Download audio from a YouTube video given the video URL and output path.
    :param video_url: The URL of the YouTube video.
    :param output_dir: The path where the audio file will be saved.
    :return: The processed audio file path if successful, None otherwise.
    """
    try:
        # Download  WebM audio stream from YouTube link
        ytVideo = YouTube(video_url)
        stream = ytVideo.streams.get_by_itag(251)  # WebM audio stream
        dest_file = f"{slugify(stream.title)}.webm"
        stream.download(output_path=output_dir, filename=dest_file)

        # Post-process audio stream to MP3 file
        final_file = process_audio(output_dir, dest_file)

        # Cleanup
        os.unlink(f"{output_dir}/{dest_file}")

        return final_file, stream.default_filename

    except Exception as error:
        line = f"{video_url} => {error}\n"
        write_file("invalid_youtube_links.txt", line)
        print(f"ERROR => Cannot acquire audio: {line} => {error}")
        return None, None


def process_audio(output_dir, source_file):
    """
    Convert audio file from webm to mp3 using ffmpeg.

    Parameters:
    - output_dir: str, the path where the output file will be saved
    - source_file: str, the name of the source audio file without extension

    Returns:
    str, the name of the final MP3 file
    """
    source_path = f"{output_dir}/{source_file}"
    final_path = f"{output_dir}/{source_file}.mp3"
    kw_args = {"ar": "16000", "acodec": "libmp3lame", "loglevel": "panic"}
    ffmpeg.input(source_path).filter("loudnorm").output(final_path, **kw_args).run()

    return final_path


def splice_audio_stream(file_path, destination_folder):
    """
    Slice audio into slices with `librosa`

    Parameters:
    - source_path: str, Source File
    - output_dir: str, Output directory

    Returns:
    List, List of spliced segments
    """
    # Load the audio file with librosa
    audio, sr = librosa.load(
        file_path, sr=None
    )  # Use sr=None to preserve the original sample rate

    audio_length = 29 * sr  # 29 seconds
    start_time = 0
    segment_counter = 1

    base_path = os.path.abspath(file_path)
    # base_path_without_ext = os.path.splitext(base_path)[0]
    file_id = str(uuid.uuid4())
    data = []

    while start_time < len(audio):
        end_time = min(start_time + audio_length, len(audio))
        segment = audio[start_time:end_time]

        segment_filename = f"{destination_folder}/{file_id}_{segment_counter}.mp3"
        # Save the segment with soundfile
        sf.write(os.path.join(destination_folder, segment_filename), segment, sr)

        data.append(
            {
                "original_audio_name": base_path,
                "segment_path": segment_filename,
                "audio_transcript": None,
            }
        )
        segment_counter += 1
        start_time += audio_length

    return data
