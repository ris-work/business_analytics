## Why is the code bad?
Code looks bad because this was basically hacked together in a weekend and does a lot of math.
If you wrote a shader, you know.
Optimized for absolute low latency. Minimally intrusive.
Also: FFIs and serialization. This project uses at least 3 different programming languages.
The site should work on old chromebooks. 
Lots of optimizations to free the server from extra load and reduce resource consumption. 
No build step, things should work as-is when `tar -xzf`'d.
Packages are managed by your system package manager, including SciPy. 
Expect portability across platforms.


### Why not Octave over SciPy?
Why should I introduce a hard GPL dependency?

### Why not Rust?
Would have taken >10x more time.
