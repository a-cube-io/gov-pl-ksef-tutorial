# ACube PL API

## Integration Steps

#### Purpose

This tutorial explains how to integrate your system with polish e-invocing using A-Cube PL API as a provider.

#### About this tutorial

This example integration is written in native PHP language in the simplest way just so you can focus on understanding
the
reqiuired elements you need to build at your side, plus proper sequence of activation of them

To wrap things up, for successfull integration with KSeF by A-Cube PL API you will need following elements in your
system:

* Moduł do przechowyania kredencjałów potrzebnych do zalogowania się do A-Cube PL API.
* Moduł do integracji z A-Cube
* Moduł do przychodzących faktur
* Moduł do wysłanych faktur
* Powiadomienia (webhook)

### Pre-requisites

Before you start make sure you have following information that needs to be pasted into `.env` file

1. `ACUBE_USER_EMAIL` - This is you customer's username/email to A-Cube API.
2. `ACUBE_USER_PASSWORD` - Your password to A-Cube API
3. `SAMPLE_NIP` - This should be real NIP (Tax ID) number of your company registered with KSeF.
4. `SAMPLE_KSEF_TOKEN` - This should be real Authorization Token generated under your KSeF Web account.

**Where do I get these information?**

* 1, 2 - You need to obtain these credentials from A-Cube. (https://acubeapi.com/)
* 3 - You obviously should have it already if you are legally registered business in Poland
* 4 - You need to obtain them from KSeF Web App (https://ksef-demo.mf.gov.pl/web/login).

### Process

We prepared set of commands explaining each step of the process. You should implement these steps in such order as
numbers of the commands into your system. For this demo purpose we will be launching command one by one, explaing in
details what happens.

#### Login to the A-Cube

First you need to authenticate into A-Cube API Platform. As a result you will receive a JWT token that is valid for 24hours.
It is required that in your system you will have solution to store and handle this token authentication.

#### Run seeder

#### Connect Company with A-Cube

#### Submit KSeF Token

#### Submit Webhooks

#### Launch Runners

#### Invoice Synchronization