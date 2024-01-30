#!/usr/bin/python3
from scipy.fft import *
import numpy as np
import sys
import json
lines = ''.join(sys.stdin.readlines())
array = np.array(json.loads(lines))
f=[0]
f.extend(np.divide(len(array), range(1,len(array))).tolist())
print(json.dumps([np.divide(np.abs(fft(array)),len(array)).tolist(), f]))
