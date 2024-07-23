import json
from snowflake import SnowflakeGenerator

def dump(object):
    """
    A function that dumps the given object into a JSON file.
    
    :param object: The object to be dumped into the JSON file.
    :return: None
    """
    print('hello!')
    # with open("dump.json", "w") as file:
    #     file.write(json.dumps(object, indent=4))
    
def make_snowflake_id():
    return next(SnowflakeGenerator(1))

def write_file(file_path, line):
    with open(file_path, 'a') as file:
        file.write(line)