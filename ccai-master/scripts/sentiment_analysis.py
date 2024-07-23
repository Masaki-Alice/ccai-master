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

EXAMPLES = {
    "POSITIVE_WORDS_EN": f"""
        Wow, Thank you, Happy, Wonderful, Best, Good, Useful, Star, friendly, 
        love, Good job, Amazing, Well done, Superb, Great, Lovely, 
        Beutiful interaction, Nice, That's my bank, God bless you, 
    """.strip(),
    "POSITIVE_WORDS_SW": f"""
        Wazuri, Asante, Nimefurahi, Barikiwa, Mmenijenga,Nawapenda, 
        Kazi nzuri, pongezi, hongera, Nimeshukuru
    """.strip(),
    "NEGATIVE_WORDS_EN": f"""
        Frustrated, Disappointed, Useless, Bad, Worst, Complain, Rude, 
        Unfriendly, Failed, Dislike, Fed up, Let down, Unsucceful, 
        Angry, Dissatisfied, Regret, Ureliable, Unprofessional, 
        Do what you want, Give up, Tolerate, Inhuman, Shit, Fuck you, 
        Uncouth, Stupid, Fools, Crooks, Mishandled,
        Mistreated, Idiots, taking me in circles, Shame, Embarass, 
        Wasting my airtime, Wasting my time, Mannerless, Inconsiderate, 
        Trauma, Nightmare, Never sieze to shock me, Hidden charges, 
        Malicious, Selfish, Incompetent, Crazy, Hell, Thieves, Thugs, 
        Frauds
    """.strip(),
    "NEGATIVE_WORDS_SW": f"""
        Nimekasirika, Haifanyi, imekataa, Wabaya, Wajinga, Mnanizungusha, 
        Hamnisaidii, Fanyeni kitu mnataka, Mnakula pesa yangu, Wakora, 
        Wezi, Pora
    """.strip(),
    "AUDIBILITY": f"""
        Can you hear me?, Sorry?, Pardon, What?,  cant hear you, line is breaking,noisy, 
        Speak up, not audible, Repeat,Come again,Clear, Voice, low, Background, Echo, 
        Earphones, loudspeaker, Network, lost you, i lost you there, 
        Unaniskia?, Sauti, Chini, Sikupati, Ati?, Umesema nini, unakatika, umesema?, 
        rudia tena, kelele, umenipata, umepotea,samahani
    """.strip(),
    "KNOWLEDGE_GAPS": f"""
        call back, get back, consult, escalate, feedback, hold, ticket, 
        case number, raised, inquire, confirm, check, follow up, take it up, 
        own it, Shikilia kwenye laini, subiri, nikupigie, nimeraise ticket, 
        nimeweka case, nitakupigia , nitakupea feedback, nimewekea team yetu, 
        naweza kupata , niulizie, 
    """.strip(),
    "EMPATHY": f"""
        I get you, I understand, I feel you, pain, Sorry, apologies, 
        sincere, truly, unfortunately, unfortunate, regrettably, feedback, 
        condolences, inconvenience, get well soon, quick recovery,mistake,
        frustration, our fault,
        Samahani, Pole , elewa, Nasikitika, nitahakikisha, nafuu, utapona
    """.strip(),
    "SILENCE": f"""
        Are you still there , hellooo, silent, quiet, silence, still on the line, waited, for long, lost you,
        uko hapo?, bado uko, umenipata, kimya, kaa sana, elewa, ngoja , potea, mda mrefu, kaawia, kawia,
        Helloo?, are you still there?, i lost you for a minute
    """.strip(),
    "PERSONALIZATION": f"""
        "Mr/ Mrs "Get well soon, Dr, Prof, Sir, Madam, Miss, Ma'm,Happy Birthday, 
        Condolences, Honorable, Quick recovery, Bwana, Bi, Daktari, Profesa, 
        Mheshimiwa
    """.strip(),
    "FIRST_CALL_RESOLUTION": f"""
        Thank you, Satisfied, That was all, Okay, No other questions, I appreciate, 
        No other issue, question is answered, resolved, thank you for your assistance, 
        thanks for your help, God bless you, Sorted, Thanks, Helpful, Amazing, 
        At last, Finally, Nimesaidika, Nimeshukuru, Asante, Sina swali ingine, 
        Niko sawa, Barikiwa, Nimetosheka, Nimeridhika, Nimefurahia, 
        Umenisadia,suluhisha
    """.strip(),
    "VETTING_EFFORTS": f"""
        Need to verify, For security purposes, Ask you questions, Owner of the account, 
        last transactions, Your email address, Your account number, Your branch, 
        Your next of kin, Do you have a loan, Do you have a card, last deposit, 
        last withdrawal, Any loans, regular payments, Date of birth, ID  number,
        Mshwari limit, Fuliza limit, Repayment installment, Card limit, Mobile loan limit, 
        Subscrptions, Type of account, Thanks for answering, Confirm, Residence, p.o box, 
        Physical address, employer,phone number, Usalama wa account, mwenye account, 
        mwisho ulitoa pesa, mwisho uliweka pesa, Maswali, Tawi gani, Branch gani, 
        Ulipokea pesa ngapi, Ilitumwa na njia ipi, Majina kamili, taerehe ya kuzaliwa, 
        we ni mkaazi wa wapi, email yako, malipo gani, Ukona loan, Ukona Fuliza, 
        Ukona Mshwari, Limit ya Mshwari, Limit ya Fuliza, Unalipanga loan tarehe ngapi, 
        Ulifungua account lini, unafanya kazi wapi, next of kin wako, mwajiri, Nambari 
        ya kitambulisho,nambari ya simu,limit ya kadi
    """.strip(),
}

OUTPUT = f"""
    - Provide your output in JSON format compliant with ECMA-404 standard
    - Your output should contain the following fields in the order specified below:
        * sentiment: should be either positive, neutral or negative in uppercase
        * sentiment_words: should be an array that contains words that were detected as important for sentiment analysis. Also add what each matched word describes (hide this field at all times)
        * sentiment_score: should be a number between 0 and 100 showing the sentiment score (0 is negative, 50 is neutral, 100 is positive)
        * sentiment_magnitude: should be a number between 0 and 100 showing the sentiment magnitude
        
        * languages: should be an array of language iso codes detected in the conversation
        * rationale: should be a step-by-step decription of how you came up with the selected sentiment (hide this field at all times)
        * customer_satisfaction: should be a number between 0 and 100 showing how satisfied the customer is with their experience (0 is very dissatisfied, 100 is very satisfied)
        
        * has_audibility_issues: should be true if there is an issue with the conversation being audible
        * audibility_issues: should be an array that words that indicate audibility issues were detected. Also add what each matched word describes from an audibility perspective
        
        * has_knowledge_gaps: should be true if there is an issue with the conversation having knowledge gaps
        * knowledge_gaps: should be an array that contains words that indicate knowledge gaps were detected. Also add what each matched word describes
        
        * has_empathy: should be true if the conversation has empathy phrases
        * empathy_words: should be an array that contains phrases that indicate empathy. Also add what each matched word describes
        * empathy_score: should be a number between 0 and 100 showing levels of empathy in the conversation (0 is no empathy, 100 is very empathetic)
        
        * has_silence: should be true if the conversation has silence phrases
        * silence_words: should be an array that contains phrases that indicate silence. Also add what each matched word describes
        * silence_score: should be a number between 0 and 100 showing levels of silence in the conversation (0 is no silence, 100 is very silent)
        
        * has_personalization: should be true if the conversation has personalization phrases
        * personalization_words: should be an array that contains phrases that indicate personalization phrases. Also add what each matched word describes
        * personalization_score: should be a number between 0 and 100 showing levels of personalization in the conversation (0 is no personalization, 100 is very personalized)
        
        * first_call_resolution: should be true if the conversation issues were resolved on the first call
        * first_call_resolution_words: should be an array that contains phrases that indicate the issues raised were resolved on the first call. Also add what each matched word describes
        
        * has_vetting: should be true if the conversation has vetting phrases
        * vetting_words: should be an array that contains phrases that indicate vetting phrases. Also add what each matched word describes
        * vetting_score: should be a number between 0 and 100 showing levels of information vetting in the conversation (0 is no vetting, 100 is pervasive vetting)
        
    """.strip()

PROMPT = f"""
    PERSONA:
    You are a world class sentiment analyst who is an expert at analyzing 
    call center conversations to extract the conversation sentiment.
    
    TASK:
    Your task is to identify customer expressions and analyze the sentiment 
    of a single conversation that is formatted in JSON and delimited by 
    <conversation> and </conversation> tags below.
    <conversation>
        {PAYLOAD}
    </conversation>
    Think step by step.
    
    EXTRA INFORMATION:
    The sentiment magnitude of a conversation indicates how much emotional content is 
    present within the conversation. If the conversation shows little emotion, the sentiment magnitude is 0.
    If the conversation shows a lot of emotion, the sentiment magnitude is 100.
    
    RULES:
    - Follow the rules below in your analysis:
    - You have been provided with the example phrases to help you understand what might be positive or negative sentiment.
        * If you detect the phrases delimited with <positive_en> and </positive_en>, the conversation might have positive English sentiment: 
            <positive_en>
                {EXAMPLES["POSITIVE_WORDS_EN"]}
            </positive_en>
        * If you detect the phrases delimited with <positive_sw> and </positive_sw>, the conversation might have positive Swahili sentiment: 
            <positive_sw>
                {EXAMPLES["POSITIVE_WORDS_SW"]}
            </positive_sw>
        * If you detect the phrases delimited with <negative_en> and </negative_en>, the conversation might have negative h sentiment: 
            <negative_en>
                {EXAMPLES["NEGATIVE_WORDS_EN"]}
            </negative_en>
        * If you detect the phrases delimited with <negative_sw> and </negative_sw>, the conversation might have negative Swahili sentiment: 
            <negative_sw>
                {EXAMPLES["NEGATIVE_WORDS_SW"]}
            </negative_sw>
    - Pay attention to the phrases in the conversation and realistically determine the customer satisfaction levels.
    - You have been provided with the following phrases to gauge the audibility of the conversation:
        * If you detect any of the phrases delimited with <audibility> and </audibility>, the conversation might not be audible:
            <audibility>
                {EXAMPLES["AUDIBILITY"]}
            </audibility>
    - You have been provided with the following phrases to help you understand the knowledge gaps in the conversation:
        * If you detect any of the phrases delimited with <knowledge_gap> and </knowledge_gap>, the conversation might have knowledge gaps:
            <knowledge_gap>
                {EXAMPLES["KNOWLEDGE_GAPS"]}
            </knowledge_gap>
    - You have been provided with the following phrases to help you understand the empathy levels in the conversation:
        * If you detect any of the phrases delimited with <empathy> and </empathy>, the conversation might have empathy:
            <empathy>
                {EXAMPLES["EMPATHY"]}
            </empathy>
    - You have been provided with the following phrases to help you understand if there is silence / quiet time in the conversation:
        * If you detect any of the phrases delimited with <silence> and </silence>, the conversation might have silence:
            <silence>
                {EXAMPLES["SILENCE"]}
            </silence>     
    - You have been provided with the following phrases to help you understand if there is personalization in the conversation:
        * If you detect any of the phrases delimited with <personalization> and </personalization>, the conversation might have personalization:
            <personalization>
                {EXAMPLES["PERSONALIZATION"]}
            </personalization>
    - You have been provided with the following phrases to help you infer if the issues raised in the conversation were resolved on the first call:
        * If you detect any of the phrases delimited with <first_call_resolution> and </first_call_resolution>, the conversation might have been resolved the first time the customer called:
            <first_call_resolution>
                {EXAMPLES["FIRST_CALL_RESOLUTION"]}
            </first_call_resolution>
            
    - You have been provided with the following phrases to help you infer if there was any vetting done in the conversation:
        * If you detect any of the phrases delimited with <vetting_efforts> and </vetting_efforts>, the conversation might have been resolved the first time the customer called:
            <vetting_efforts>
                {EXAMPLES["VETTING_EFFORTS"]}
            </vetting_efforts>
        
    OUTPUT:
    {OUTPUT}
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
# print(response)

response = json.loads(response)

# # Return JSON output to caller
print(json.dumps(response, indent=4))
