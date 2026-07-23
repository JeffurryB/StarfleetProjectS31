
# README

Each class/exam pair will need to be seperated into the sdq_classes and the sdq_exams directories accordingly.  This system used to bring content from the website to Second Life via script, however that's a laggy mess and not really needed.  This new system allows users to take classes via web interface and take the exam via web interface.

### Classes Formattting
Title of File: class name, number, and author in the format ABC-123_FIRTNAME_LASNAME.txt

All other text for the file on its own line.


### Exams Format

```
QUESTION:
A:Answer A
B:Answer B
C:Answer C
D:Answer D
ANSWER:D
===
QUESTION:
A:Answer A
B:Answer B
C:Answer C
D:Answer D
ANSWER:C
===
```
Every question on the exam will need to be formatted using this template as the PHP Grading system will crash with extra characters and out of place questions.
