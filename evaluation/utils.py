"""
Author: Michel Peltriaux
Organization: Spatial data infrastructure Rhineland-Palatinate, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 01.07.19

"""
import hashlib


def sha256(_input: str):
    """ Creates a sha256 hash from the input

    Args:
        _input (str): A string
    Returns:
         A sha256 hash string
    """
    m = hashlib.sha256()
    m.update(_input.encode("UTF-8"))
    return m.hexdigest()


def resolve_boolean_value(val: str):
    """ Resolve a string which represents a boolean value

    Args:
        val: The value
    Returns:
         True, False or None
    """
    val = val.upper()
    if val == "TRUE":
        return True
    elif val == "FALSE":
        return False
    else:
        return None