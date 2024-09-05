## What is this?
This repo contains the code used for a business analytics / intelligence system used at SR Medicals, Trincomalee.
This repo also contains a simple command-line DFT wrapper for `numpy` which does periodicity analysis on sales.

## Screenshots
TBD

## Database
This uses a periodically logged SQLite database. It contains hourly sales data by unique itemcode (table: `hourly`). It also contains certain information necessary to have the HTML5 barcode scanner to produce analytics, including the barcode details, the cost price, etc. Cost price and selling price averages per hour are also stored in the database and it can also produce a graph by date for the averages (for long term trends).

## License
Rishikeshan licenses all his contributions under OSLv3 or (at your option) specified in LICENSE.txt.
Waiting for Vandana to respond re: her code.

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

## Developers
Rishikeshan [@ris-work](https://github.com/ris-work) [site](https://rishikeshan.com)
Vandana Panchal [@veenupanchal](https://github.com/veenupanchal) [site](https://veenu.pa.nch.al)
