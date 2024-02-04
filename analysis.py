#!/usr/bin/python3
# use: Seasonal analysis. Uses FFT.
# input: [p1, p2, p3...], pn is evenly time-distributed (e.g. daily)
# output: [[a1, a2, a3, ...], [w1, w2, w3, ...]
# where: an is amplitude, wn is wavelength. e.g. a0 = 2 means + or - 2
# (varies by 4 in total)
# caveat: w[0] should be infinity. JSON serializer does not handle it.
# so, it's set to zero.
# Copyright Rishikeshan Sulochana/Lavakumar (2024)
# License: Apache License 2.0

from scipy.fft import *
import numpy as np
import sys
import json
lines = ''.join(sys.stdin.readlines())
array = np.array(json.loads(lines))
w=[0]
w.extend(np.divide(len(array), range(1,len(array))).tolist())
print(json.dumps([np.divide(np.abs(fft(array)),len(array)).tolist(), w]))

# Sample, how to use (PHP): 
#$spectrum: output.
#$daily_data_only: evenly spaced time series.
#$desspec = array(
#	0 => array("pipe", "r"),
#	1 => array("pipe", "w"),
#	2 => array("pipe", "r")
#);
#$process = proc_open('python3 analysis.py', $desspec, $pipes);
#if(is_resource($process)){
#	fwrite($pipes[0], json_encode($daily_data_only));
#	fclose($pipes[0]);
#	$spectrum = trim(stream_get_contents($pipes[1]));
#	fclose($pipes[1]);
#	proc_close($process);
#}
#else{
#	echo "Process creation failed.";
#}
