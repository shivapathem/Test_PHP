USE [AllocateLink]

GO

 

/****** Object:  StoredProcedure [dbo].[usp_Import_TP_A7_StaffConfig]    Script Date: 03/10/2023 15:42:45 ******/

SET ANSI_NULLS ON

GO

 

SET QUOTED_IDENTIFIER ON

GO

 

CREATE OR ALTER PROCEDURE [dbo].[usp_Import_TP_A7_StaffConfig]

       @SystemID            INT,

       @StartDate           DATETIME,

       @EndDate             DATETIME

AS

-- =============================================

-- Author:      Michael Hoskin

-- Create date: 24/01/2022

-- Description: SP to populate the table with data from Teampay.

--                           This can then be accessed by Allocate7.

-- =============================================

-- Author:      Michael Hoskin

-- Create date: 25/11/2022

-- Description: Modify script to also import Allocate-Grey records

--                                  between 01/04/2021 and 03/02/2022

-- =============================================

-- Author:      Michael Hoskin

-- Create date: 21/12/2022

-- Description: Modify script to also import Allocate-Grey records

--                                  between 01/04/2021 and 01/04/2023

-- =============================================

BEGIN

        SET NOCOUNT ON;

 

 

              --Delete existing records where the SchedulingSystemID has changed

              UPDATE T1

                  SET [SchedulingSystemID] = T2.[SchedulingSystemID],

                             [IsActive] = 0,

                             [LastModDate] = T2.[LastModDate],

                             [LastModBy] = T2.[LastModBy],

                             [History] = T2.[History]

                FROM [AllocateLink].[dbo].[TP_A7_StaffConfig] T1 INNER JOIN

                             [Teampay].[dbo].[StaffConfig] T2 ON T1.[ConfigID] = T2.[ConfigID]

              WHERE (T1.[IsActive] = 1)

                 AND ((T1.[StartDate] BETWEEN @StartDate AND @EndDate)

                  OR (T1.[EndDate] BETWEEN @StartDate AND @EndDate))

                 AND (T2.[SchedulingSystemID] NOT IN (1,2,5));

             

             

             

              --Update existing records

              UPDATE T1

                  SET [SchedulingSystemID] = T2.[SchedulingSystemID],

                             [StaffID] = T2.[StaffID],

                             [StartDate] = T2.[StartDate],

                             [EndDate] = ISNULL(T2.[EndDate], '99991231'),

                             [EDPMinimum] = T2.[EDPMinimum],

                             [EDPMinimumExcBreaks] = T2.[EDPMinimumExcBreaks],

                             [PartTimeEDP] = T2.[PartTimeEDP],

                             [AccGroupID] = T2.[AccGroupID],

                             [AveDayLen] = T2.[AveDayLen],

                             [AccDays] = T2.[AccDays],

                             [TermsCondsVersionID] = T2.[TermsCondsVersionID],

                             [PaymentTypeID] = T2.[PaymentTypeID],

                             [BreaksGroupID] = T2.[BreaksGroupID],

                             [ManualEDP] = T2.[ManualEDP],

                             [ActivityTypeID] = T2.[ActivityTypeID],

                             [ActivityType] = T2.[ActivityType],

                             [CompExpiry] = T2.[CompExpiry],

                             [TOILExpiry] = T2.[TOILExpiry],

                             [Under11TOILExpiry] = T2.[Under11TOILExpiry],

                             [Over12TOILExpiry] = T2.[Over12TOILExpiry],

                             [AutoEDPTOIL] = T2.[AutoEDPTOIL],

                             [SendEmails] = T2.[SendPayslipEmails],

                             [IsActive] = T2.[IsActive],

                             [CreatedDate] = T2.[CreatedDate],

                             [LastModDate] = T2.[LastModDate],

                             [LastModBy] = T2.[LastModBy],

                             [History] = T2.[History],

                             [IsTermTime] = T2.[IsTermTime]

                FROM [AllocateLink].[dbo].[TP_A7_StaffConfig] T1 INNER JOIN

                             [Teampay].[dbo].[StaffConfig] T2 ON T1.[ConfigID] = T2.[ConfigID]

              WHERE (T2.[SchedulingSystemID] = @SystemID)

                 AND ((T1.[StartDate] BETWEEN @StartDate AND @EndDate)

                  OR (T1.[EndDate] BETWEEN @StartDate AND @EndDate))

                 AND ((T1.[LastModDate] <> T2.[LastModDate])

                  OR (T1.[EndDate] <> ISNULL(T2.[EndDate], '99991231'))

                      OR (T1.[IsActive] <> T2.[IsActive]));

             

             

             

              --Insert new records that do not exist

              INSERT INTO [AllocateLink].[dbo].[TP_A7_StaffConfig] (

                         [ConfigID]

                        ,[SchedulingSystemID]

                        ,[StaffID]

                        ,[StartDate]

                        ,[EndDate]

                        ,[EDPMinimum]

                        ,[EDPMinimumExcBreaks]

                        ,[PartTimeEDP]

                        ,[AccGroupID]

                        ,[AveDayLen]

                        ,[AccDays]

                        ,[TermsCondsVersionID]

                        ,[PaymentTypeID]

                        ,[BreaksGroupID]

                        ,[ManualEDP]

                        ,[ActivityTypeID]

                        ,[ActivityType]

                        ,[CompExpiry]

                        ,[TOILExpiry]

                        ,[Under11TOILExpiry]

                        ,[Over12TOILExpiry]

                        ,[AutoEDPTOIL]

                        ,[SendEmails]

                        ,[IsActive]

                        ,[CreatedDate]

                        ,[LastModDate]

                        ,[LastModBy]

                        ,[History]

                        ,[IsTermTime]

                      ) 

              SELECT T1.[ConfigID]

                        ,T1.[SchedulingSystemID]

                        ,T1.[StaffID]

                        ,T1.[StartDate]

                        ,ISNULL(T1.[EndDate], '99991231') as [EndDate]

                        ,T1.[EDPMinimum]

                        ,T1.[EDPMinimumExcBreaks]

                        ,T1.[PartTimeEDP]

                        ,T1.[AccGroupID]

                        ,T1.[AveDayLen]

                        ,T1.[AccDays]

                        ,T1.[TermsCondsVersionID]

                        ,T1.[PaymentTypeID]

                        ,T1.[BreaksGroupID]

                        ,T1.[ManualEDP]

                        ,T1.[ActivityTypeID]

                        ,T1.[ActivityType]

                        ,T1.[CompExpiry]

                        ,T1.[TOILExpiry]

                        ,T1.[Under11TOILExpiry]

                        ,T1.[Over12TOILExpiry]

                        ,T1.[AutoEDPTOIL]

                        ,T1.[SendPayslipEmails]

                        ,T1.[IsActive]

                        ,T1.[CreatedDate]

                        ,T1.[LastModDate]

                        ,T1.[LastModBy]

                        ,T1.[History]

                        ,T1.[IsTermTime]

                FROM [Teampay].[dbo].[StaffConfig] T1

              WHERE ((T1.[StartDate] BETWEEN @StartDate AND @EndDate)

                  OR (T1.[EndDate] BETWEEN @StartDate AND @EndDate))

                 AND (T1.[SchedulingSystemID] = @SystemID)

                 AND NOT EXISTS (SELECT T2.[ConfigID]

                                                   FROM [AllocateLink].[dbo].[TP_A7_StaffConfig] T2

                                                  WHERE T1.[ConfigID] = T2.[ConfigID])

       

END

 

 

GO

 

 