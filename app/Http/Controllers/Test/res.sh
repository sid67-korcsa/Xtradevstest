#!/bin/bash
###
#sysres download script
###


setLockContent() {
    touch ${_pid}
    echo ${_pidId} > ${_pid}
}

delLockFile() {
    if [ -f ${_pid} -a -w ${_pid} ]
    then
        unlink ${_pid}
    fi
}

addLogDir() {
    if [ ! -d "${_logDir}" ]
    then
	mkdir ${_logDir} 2>/dev/null
    fi
}

downloadContent() {
    _url="https://fastly-cdn.system-rescue.org/releases/13.01/systemrescue-13.01-amd64.iso"
    #_opts="-c -vv -S -t 7 -a ${_logFile} "
    _opts="-c -vv -S -t 7 "
    ${_wget} ${_opts} ${_url} | tee ${_logFile}  
}


_wget=`which wget`
_downDir=$1
_pidId=$$
_sumComArg=$#
_lastProcRet=$!
_pid="/tmp/${_pidId}.lock"
_logDir="log"
_logFile=${_logDir}"/wget-log-`date +%Y%m%d%H%M`"





if [ -x ${_wget} ] 
then
    setLockContent    
    addLogDir
    echo -e "${_pidId}\n\n"
    echo -e "\tLOG: ${_logFile}\n"
    downloadContent
    echo $!
else 
    echo -e "Missing wget system binary!\n"
    exit 1
fi


delLockFile


exit 0

