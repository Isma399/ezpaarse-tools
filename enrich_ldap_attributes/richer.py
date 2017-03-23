#!/usr/bin/python
# coding: utf8
''' It excepts one arguement : the log to parse.
    It appends to each line some ldap attributes related to the login used.
    The login is the third field of each line.
'''
import sys, string, re, ldap


def attribute_ldap ( uid ):
    ''' 
    takes uid and return needed attributes in a hash
    Attributes = ['eduPersonAffiliation','supannEtuCursusAnnee','uboLibelleCmp']
    '''
    YOUR_LDAPSERVER = ""
    YOUR_BASEDN = ".." #For instance ou=student,dc=domain,dc=com

    try:
        l = ldap.initialize('ldap://'+YOUR_LDAPSERVER)
        l.simple_bind_s()
        basedn = YOUR_BASEDN
        filter = "(uid=" + uid + ")"
        attributs = ['eduPersonAffiliation','supannEtuCursusAnnee','uboLibelleCmp']
        results = l.search_s(basedn,ldap.SCOPE_SUBTREE,filter,attributs)
    except ldap.LDAPError, e:
        print e
    l.unbind_s()
    return results


def enrich_uid ( uid ):
    '''Function enrichUid
            Add in a dict, ldap attributes, key is the uid
            following this rules :
            -if student -> return department and cursus
            -if other than student -> return department and status (employee,..)
            -default returned = - -'''
    results =  attribute_ldap(uid)
    affiliation = results[0][1]['eduPersonAffiliation'][0]
    if affiliation == "student" :
        affiliation=results[0][1]['supannEtuCursusAnnee'][0].replace("{SUPANN}","")
    ufr=results[0][1]['uboLibelleCmp'][0]
    return ufr, affiliation 


if __name__ == '__main__':
    try:
        with open(sys.argv[1], 'r') as f:
            lines = map(lambda ch: ch.strip('\n'), f.readlines())
    except IndexError:
        print "Need a log to parse!"
        sys.exit(1)

    reg = re.compile('.+\s-\s(?P<id>[0-9a-z-]+) .*')
    regUnknown = re.compile('([0-9\.]*\s-\s)-')
    uid_dict = {'NONE':'NONE'}
    
    with open(sys.argv[1], 'w') as rapport:
        for line in lines:
            regMatch = reg.match(line)
            if regMatch:
                    uid = regMatch.group(1)
                    if uid=="-":
                        rapport.write(regUnknown.sub(r'\1UNKNOWN', line)+" 'NONE' 'NONE'\n")
                    else:
                        if uid in uid_dict:
                            rapport.write(line + " '" + "' '".join(uid_dict[uid]) + "'\n")
                        else:
                            uid_dict[uid] = enrich_uid(uid)
                            rapport.write(line + " '" + "' '".join(uid_dict[uid]) + "'\n")
    


