<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>DLP Test</title>
    <meta name="author" content="name" />
    <meta name="description" content="description here" />
    <meta name="keywords" content="keywords,here" />
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css" />
    <!--Replace with your tailwind.css once created-->
</head>


<body class="bg-gray-100 font-sans leading-normal tracking-normal">


    <!--Container-->
    <div class="container w-full md:max-w-3xl mx-auto pt-20">

        <div class="w-full px-4 md:px-6 text-xl text-gray-800 leading-normal" style="font-family:Georgia,serif;">

            <!--Title-->
            <div class="font-sans space-y-5">
                <h1 class="font-bold font-sans break-normal text-gray-900 pt-6 pb-2 text-3xl md:text-4xl">
                    Original Transcript
                </h1>
                <p>
                    John Doe, born on January 15, 1985, resides at 123 Main Street, Nairobi, Kenya. His email address is
                    john.doe@example.com, and his phone number is +254 (0) 712 345 678. He works as a software engineer
                    at
                    XYZ Corporation, located at 456 Elm Avenue, Nairobi. His bank account number is 1168019621. John
                    will be
                    25 years old in 2 months.
                </p>
                <p>
                    John's national ID number is 12345678, and his credit card number is 1234-5678-9012-3456. He holds a
                    Master's degree in Computer Science from University of Nairobi, graduating in 2010. His current
                    salary
                    is KSh 1,000,000 per year. John identifies as Hispanic.
                </p>
                <p>

                    Jane Smith, born on March 20, 1990, lives at 789 Oak Lane, Mombasa, Kenya. Her email is
                    jane.smith@example.com, and her phone number is +254 (0) 712 987 654. She is a medical doctor
                    specializing in cardiology at Coast General Hospital, located at 789 Palm Street, Mombasa. Jane's
                    nationality is American.
                </p>

                <p>
                    Jane's national ID number is 87654321, and her credit card number is 9876-5432-1098-7654. She
                    graduated
                    from University of Nairobi School of Medicine in 2015. Her annual income is KSh 2,000,000. Jane
                    identifies as Luo. Jane's sister is 45 years old.
                </p>
            </div>

            <div class="font-sans space-y-5">
                <h1 class="font-bold font-sans break-normal text-gray-900 pt-6 pb-2 text-3xl md:text-4xl">
                    Redacted Transcript
                </h1>
                <p>
                    John Doe, born on January 15, 1985, resides at 123 Main Street, Nairobi, Kenya. His email address is
                    ####################, and his phone number is ####################. He works as a software engineer
                    at XYZ Corporation, located at 456 Elm Avenue, Nairobi. His bank account number is ##########. John
                    will be ############ in 2 months.
                </p>

                <p>
                    John's national ID number is 12345678, and his credit card number is ###################. He holds a
                    Master's degree in Computer Science from University of Nairobi, graduating in 2010. His current
                    salary is KSh 1,000,000 per year. John identifies as ########.
                </p>

                <p>
                    Jane Smith, born on March 20, 1990, lives at 789 Oak Lane, Mombasa, Kenya. Her email is
                    ######################, and her phone number is ####################. She is a medical doctor
                    specializing in cardiology at Coast General Hospital, located at 789 Palm Street, Mombasa. Jane's
                    nationality is ########.
                </p>

                <p>
                    Jane's national ID number is 87654321, and her credit card number is ###################. She
                    graduated from University of Nairobi School of Medicine in 2015. Her annual income is KSh 2,000,000.
                    Jane identifies as Luo. Jane's sister is ############.
                </p>
            </div>

            <div class="font-sans space-y-5">
                <h1 class="font-bold font-sans break-normal text-gray-900 pt-6 pb-2 text-3xl md:text-4xl">
                    Redacted Info Types
                </h1>
                @dump(config('settings.dlp.info_types'))
            </div>





</body>

</html>
