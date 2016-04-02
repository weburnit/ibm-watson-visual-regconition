# ibm-watson-visual-recognition
IBMWatson Visual Recognition

[![Latest Stable Version](https://poser.pugx.org/weburnit/ibm-visual-recognition/v/stable)](https://packagist.org/packages/weburnit/ibm-visual-recognition) 
[![Total Downloads](https://poser.pugx.org/weburnit/ibm-visual-recognition/downloads)](https://packagist.org/packages/weburnit/ibm-visual-recognition) 
[![Latest Unstable Version](https://poser.pugx.org/weburnit/ibm-visual-recognition/v/unstable)](https://packagist.org/packages/weburnit/ibm-visual-recognition) 
[![License](https://poser.pugx.org/weburnit/ibm-visual-recognition/license)](https://packagist.org/packages/weburnit/ibm-visual-recognition)

### Step 1: Install

This bundle is available on Packagist. You can install it using Composer:

```bash
$ composer require weburnit/ibm-visual-recognition
```

### Step 2: Usage



``` php
<?php
$service = new VisualRecognition('78ba7889-e331-4f58-958b-254f9231b1d2', 'MHsln6fPvCEp', '2015-12-02');

$service->classifyImage('/path/to/your/image/file.png', ['acne_1234151234']);
$service->classifyImages(['array:/all/file/paths/to/your/classify/images.png'], ['acne_1234151234'])
```