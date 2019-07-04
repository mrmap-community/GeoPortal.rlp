"""
This file contains methods that are needed in all apps.

Author: André Holl
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: andre.holl@vermkv.rlp.de
Created on: 03.07.19
"""

import re, configparser

def get_mapbender_config_value(base_dir, variable):
    """ Returns the requested mapbender.conf value

    Args:
        base_dir: File path
        variable: Requested variable name
    Returns:
         The variable value
    """
    define_pattern = re.compile(r"""\bdefine\(\s*('|")(.*)\1\s*,\s*('|")(.*)\3\)\s*;""")
    assign_pattern = re.compile(r"""(^|;)\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=\s*('|")(.*)\3\s*;""")

    php_vars = {}
    for line in open(base_dir + "mapbender/conf/mapbender.conf", encoding="utf-8"):
        for match in define_pattern.finditer(line):
            php_vars[match.group(2)] = match.group(4)
        for match in assign_pattern.finditer(line):
            php_vars[match.group(2)] = match.group(4)

    return php_vars[variable]

def get_php_config_value(path, section, variable):
    """ Returns the requested php.ini value

    Args:
        path: File path
        section: Section name
        variable: Requested variable name
    Returns:
         The variable value
    """
    config = configparser.ConfigParser()
    config.read(path)
    value = config[section][variable]
    return value