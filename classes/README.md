Copyright (c) 2024 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
GPLv3, see LICENSE

Author: Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>

INSTALLATION
------------

This is an example plugin with minimal features for the ILIAS PageComponent Plugin Slot.

```
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
git clone https://github.com/ILIAS-eLearning/ChatClient.git ChatClient

```For DEV; requires node 18+
cd ChatClient
npm install
npm run build
```

### Branching
This plugin follows the same branching-rules like the ILIAS-projekt itself:
- trunk: Main-Development-Branch
- release_X-X: Stable Release

### Common Errors and solutions that worked here
**PLUGINCLASS** was not found in the control structure
-> Remove Ctrl from class