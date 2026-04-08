USE [AllocateLink]

GO

 

/****** Object:  StoredProcedure [dbo].[usp_Import_TP_A7_StaffConfig_Processed]    Script Date: 13/09/2023 16:48:15 ******/

SET ANSI_NULLS ON

GO

 

SET QUOTED_IDENTIFIER ON

GO

 

CREATE   OR ALTER       PROCEDURE [dbo].[usp_Import_TP_A7_StaffConfig_Processed]

 

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

 

-- Create date: 08/08/2022

 

-- Description: Modify SP to populate TEMP table first so we can Update and Insert as required.

 

-- =============================================

 

-- Author:      Michael Hoskin

 

-- Create date: 21/12/2022

 

-- Description: Change joins on the [TP_A7_StaffContract] and [TP_A7_StaffPosition] tables

 

--                                  to use the [TP_A7_StaffConfig] [EndDate] field rather than [StartDate].

 

-- =============================================

 


 

BEGIN

 

        SET NOCOUNT ON;

 

            

 

              DECLARE @MinDateTime as DATETIME

 

              SET @MinDateTime = DATEADD(year, -4, GETDATE())

 

            

 

              IF (@MinDateTime < @StartDate)

 

                      SET @MinDateTime = @StartDate

 

            

 

             

 

              --Truncate the TEMP table

 

              TRUNCATE TABLE TP_A7_TEMP_StaffConfig_Processed;

 

            

 

             

 

              --Process and Insert records into the Temp table

 

              INSERT INTO [AllocateLink].[dbo].[TP_A7_TEMP_StaffConfig_Processed] (

 

                         [TeampayStaffID]

 

                        ,[TeampayStaffNumber]

 

                        ,[TeampayEmpNumber]

 

                        ,[StartDate]

 

                        ,[EndDate]

 

                        ,[TeampayDepartmentID]

 

                        ,[OrgID]

 

                        ,[OrgPositionID]

 

                        ,[JobTitle]

 

                        ,[GradeID]

 

                        ,[Grade]

 

                        ,[GradeConditions]

 

                        ,[ActingGradeID]

 

                        ,[ActingGrade]

 

                        ,[ActingGradeConditions]

 

                        ,[EmpGroupCode]

 

                        ,[EmpGroup]

 

                        ,[EmpSubGroupCode]

 

                        ,[EmpSubGroup]

 

                        ,[EFT]

 

                        ,[IsPartTime]

 

                        ,[CostCode]

 

                        ,[ActivityType]

 

                        ,[UPACode]

 

                        ,[AccGroupID]

 

                        ,[AccGroup]

 

                        ,[EDPMinimum]

 

                        ,[EDPMinimumExcBreaks]

 

                        ,[PartTimeEDP]

 

                        ,[AveDayLen]

 

                        ,[AccDays]

 

                        ,[TermsCondsVersionID]

 

                        ,[PaymentTypeID]

 

                        ,[BreaksGroupID]

 

                        ,[ManualEDP]

 

                        ,[CreatedDate]

 

                        ,[LastModDate]

 

                        ,[LastModBy]

 

                        ,[History]

 

                        ,[SchedulingSystemID]

 

                                           ,[PaymentTypeName]

 

                                           ,[ContractCode]

 

                                           ,[AutoEDPTOIL]                                  

 

                                           ,[ConfigID]

 

                )

 

              SELECT T1.[StaffID],

 

                         T1.[StaffNumber],

 

                         T1.[EmpNumber],

 

                         T4.[StartDate],

 

                         T4.[EndDate],

 

                         T3.[DepartmentID],

 

                         T3.[OrgID],

 

                         T3.[OrgPositionID],

 

                         T2.[JobTitle],

 

                         T0.[GradeID],

 

                         T0.[Grade],

 

                         T0.[Conditions],

 

                         ISNULL(T6.[GradeID], 0) as ActingGradeID,

 

                         ISNULL(T6.[Grade], '') as ActingGrade,

 

                                           ISNULL(T6.[Conditions], 0) as ActingGradeConditionsID,

 

                         T2.[EmployeeGroup],

 

                         T8.[EmpGroupDescription],

 

                         T3.[EmpSubGroup],

 

                         T7.[EmpSubGroupDescription],

 

                         T3.[EFT],

 

                         T3.[IsPartTime],

 

                         --T3.[HoursPerWeek],

 

                         T3.[CostCode],

 

                         T4.[ActivityType],

 

                         T3.[UPACode],

 

                         T4.[AccGroupID],

 

                         T5.[AccGroup],

 

                         T4.[EDPMinimum],

 

                         T4.[EDPMinimumExcBreaks],

 

                         T4.[PartTimeEDP],

 

                         T4.[AveDayLen],

 

                         T4.[AccDays],

 

                         T4.[TermsCondsVersionID],

 

                         T4.[PaymentTypeID],

 

                         T4.[BreaksGroupID],

 

                         T4.[ManualEDP],

 

                         T4.[CreatedDate],

 

                         T4.[LastModDate],

 

                         T4.[LastModBy],

 

                         T4.[History],

 

                         T4.[SchedulingSystemID],

 

                                           T9.[PaymentTypeName],

 

                                           T3.[ContractCode],

 

                                           T4.[AutoEDPTOIL],

 

                                                                           T4.[ConfigID]

 

              FROM (((((((([AllocateLink].[dbo].[TP_A7_StaffDetails] T1 INNER JOIN [AllocateLink].[dbo].[TP_A7_StaffConfig] T4

 

                      ON T1.StaffID = T4.StaffID AND T4.IsActive = 1)

 

              INNER JOIN [AllocateLink].[dbo].[TP_A7_StaffContract] T3 ON T4.StaffID = T3.StaffID AND T3.IsActive = 1

 

                                           AND T4.StartDate BETWEEN T3.StartDate AND T3.EndDate)

 

              LEFT JOIN [AllocateLink].[dbo].[TP_A7_StaffPosition] T2 ON T4.StaffID = T2.StaffID AND T2.IsActive = 1

 

                                           AND T4.EndDate BETWEEN T2.StartDate AND T2.EndDate)

 

              LEFT JOIN [AllocateLink].[dbo].[TP_A7_REF_AccountingGroups] T5 ON T4.AccGroupID = T5.ID)

 

              INNER JOIN [AllocateLink].[dbo].[TP_A7_REF_Grades] T0 ON T0.GradeID = T3.Grade)

 

              LEFT OUTER JOIN [AllocateLink].[dbo].[TP_A7_REF_Grades] T6 ON T6.GradeID = T3.ActingGrade)

 

              LEFT OUTER JOIN [AllocateLink].[dbo].[TP_A7_REF_EmpSubGroup] T7 ON T7.EmpSubGroupCode = T3.EmpSubGroup)

 

              LEFT OUTER JOIN [AllocateLink].[dbo].[TP_A7_REF_EmpGroup] T8 ON T8.EmpGroupCode = T2.EmployeeGroup

 

                        LEFT OUTER JOIN [AllocateLink].[dbo].[TP_A7_REF_PaymentType] T9 ON T9.PaymentTypeID = T4.PaymentTypeID)

 

              WHERE (T4.[EndDate] >= @MinDateTime)

 

                AND (T1.[NetLogin] IS NOT NULL)

 

              ORDER BY T1.StaffID, T4.StartDate

 

            

 

             

 

             

 

             

 

              --Delete removed records

 

              --DELETE T1

 

              --  FROM ([AllocateLink].[dbo].[TP_A7_StaffConfig_Processed] T1 LEFT OUTER JOIN

 

              --               [AllocateLink].[dbo].[TP_A7_TEMP_StaffConfig_Processed] T2

 

              --                       ON T1.[TeampayStaffID] = T2.[TeampayStaffID]

 

              --                      AND T1.[StartDate] = T2.[StartDate]

 

              --                      AND T1.[EndDate] = T2.[EndDate])

 

              --WHERE (T1.[EndDate] >= @MinDateTime)

 

              --   AND T2.[TeampayStaffID] IS NULL

 

             

 

             

 

             

 

              --Update modified records

 

              UPDATE T1

 

                 SET        [TeampayStaffNumber] = T2.[TeampayStaffNumber],

 

                             [TeampayEmpNumber] = T2.[TeampayEmpNumber],

 

                             [TeampayDepartmentID] = T2.[TeampayDepartmentID],

 

                             [OrgID] = T2.[OrgID],

 

                             [OrgPositionID] = T2.[OrgPositionID],

 

                             [JobTitle] = T2.[JobTitle],

 

                             [GradeID] = T2.[GradeID],

 

                             [Grade] = T2.[Grade],

 

                             [GradeConditions] = T2.[GradeConditions],

 

                             [ActingGradeID] = T2.[ActingGradeID],

 

                             [ActingGrade] = T2.[ActingGrade],

 

                             [ActingGradeConditions] = T2.[ActingGradeConditions],

 

                             [EmpGroupCode] = T2.[EmpGroupCode],

 

                             [EmpGroup] = T2.[EmpGroup],

 

                             [EmpSubGroupCode] = T2.[EmpSubGroupCode],

 

                             [EmpSubGroup] = T2.[EmpSubGroup],

 

                             [EFT] = T2.[EFT],

 

                             [IsPartTime] = T2.[IsPartTime],

 

                             [CostCode] = T2.[CostCode],

 

                             [ActivityType] = T2.[ActivityType],

 

                             [UPACode] = T2.[UPACode],

 

                             [AccGroupID] = T2.[AccGroupID],

 

                             [AccGroup] = T2.[AccGroup],

 

                             [EDPMinimum] = T2.[EDPMinimum],

 

                             [EDPMinimumExcBreaks] = T2.[EDPMinimumExcBreaks],

 

                             [PartTimeEDP] = T2.[PartTimeEDP],

 

                             [AveDayLen] = T2.[AveDayLen],

 

                             [AccDays] = T2.[AccDays],

 

                             [TermsCondsVersionID] = T2.[TermsCondsVersionID],

 

                             [PaymentTypeID] = T2.[PaymentTypeID],

 

                             [BreaksGroupID] = T2.[BreaksGroupID],

 

                             [ManualEDP] = T2.[ManualEDP],

 

                             [CreatedDate] = T2.[CreatedDate],

 

                             [LastModDate] = T2.[LastModDate],

 

                             [LastModBy] = T2.[LastModBy],

 

                             [History] = T2.[History],

 

                             [SchedulingSystemID] = T2.[SchedulingSystemID],

 

                                                  [PaymentTypeName] = T2.[PaymentTypeName],

 

                                                  [ContractCode] = T2.[ContractCode],

 

                                                  [AutoEDPTOIL] = T2.[AutoEDPTOIL],

 

                                                  [EndDate] = T2.[EndDate],

 

                                                                                         [ConfigId] = T2.[ConfigId]

 

              FROM ([AllocateLink].[dbo].[TP_A7_StaffConfig_Processed] T1 INNER JOIN

 

                             [AllocateLink].[dbo].[TP_A7_TEMP_StaffConfig_Processed] T2

 

                                     ON T1.[TeampayStaffID] = T2.[TeampayStaffID]

 

                                    AND T1.[StartDate] = T2.[StartDate])

 

                                   -- AND T1.[EndDate] = T2.[EndDate])

 

                                                              

 

             

 

             

 

             

 

              --Insert missing records

 

              INSERT INTO [AllocateLink].[dbo].[TP_A7_StaffConfig_Processed] (

 

                         [TeampayStaffID]

 

                        ,[TeampayStaffNumber]

 

                        ,[TeampayEmpNumber]

 

                        ,[StartDate]

 

                        ,[EndDate]

 

                        ,[TeampayDepartmentID]

 

                        ,[OrgID]

 

                        ,[OrgPositionID]

 

                        ,[JobTitle]

 

                        ,[GradeID]

 

                        ,[Grade]

 

                       ,[GradeConditions]

 

                        ,[ActingGradeID]

 

                        ,[ActingGrade]

 

                        ,[ActingGradeConditions]

 

                        ,[EmpGroupCode]

 

                        ,[EmpGroup]

 

                        ,[EmpSubGroupCode]

 

                        ,[EmpSubGroup]

 

                        ,[EFT]

 

                        ,[IsPartTime]

 

                        ,[CostCode]

 

                        ,[ActivityType]

 

                        ,[UPACode]

 

                        ,[AccGroupID]

 

                        ,[AccGroup]

 

                        ,[EDPMinimum]

 

                        ,[EDPMinimumExcBreaks]

 

                        ,[PartTimeEDP]

 

                        ,[AveDayLen]

 

                        ,[AccDays]

 

                        ,[TermsCondsVersionID]

 

                        ,[PaymentTypeID]

 

                        ,[BreaksGroupID]

 

                        ,[ManualEDP]

 

                        ,[CreatedDate]

 

                        ,[LastModDate]

 

                        ,[LastModBy]

 

                        ,[History]

 

                        ,[SchedulingSystemID]

 

                                           ,[PaymentTypeName]

 

                                           ,[ContractCode]

 

                                           ,[AutoEDPTOIL]

 

                                                                           ,[ConfigId]

                                        

 

                )

 

              SELECT T1.[TeampayStaffID]

 

                        ,T1.[TeampayStaffNumber]

 

                        ,T1.[TeampayEmpNumber]

 

                        ,T1.[StartDate]

 

                        ,T1.[EndDate]

 

                        ,T1.[TeampayDepartmentID]

 

                        ,T1.[OrgID]

 

                        ,T1.[OrgPositionID]

 

                        ,T1.[JobTitle]

 

                        ,T1.[GradeID]

 

                        ,T1.[Grade]

 

                        ,T1.[GradeConditions]

 

                        ,T1.[ActingGradeID]

 

                        ,T1.[ActingGrade]

 

                        ,T1.[ActingGradeConditions]

 

                        ,T1.[EmpGroupCode]

 

                        ,T1.[EmpGroup]

 

                        ,T1.[EmpSubGroupCode]

 

                        ,T1.[EmpSubGroup]

 

                        ,T1.[EFT]

 

                        ,T1.[IsPartTime]

 

                        ,T1.[CostCode]

 

                        ,T1.[ActivityType]

 

                        ,T1.[UPACode]

 

                        ,T1.[AccGroupID]

 

                        ,T1.[AccGroup]

 

                        ,T1.[EDPMinimum]

 

                        ,T1.[EDPMinimumExcBreaks]

 

                        ,T1.[PartTimeEDP]

 

                        ,T1.[AveDayLen]

 

                        ,T1.[AccDays]

 

                        ,T1.[TermsCondsVersionID]

 

                        ,T1.[PaymentTypeID]

 

                        ,T1.[BreaksGroupID]

 

                        ,T1.[ManualEDP]

 

                        ,T1.[CreatedDate]

 

                        ,T1.[LastModDate]

 

                        ,T1.[LastModBy]

 

                        ,T1.[History]

 

                        ,T1.[SchedulingSystemID]

 

                                           ,T1.[PaymentTypeName]

 

                                           ,T1.[ContractCode]

 

                                           ,T1.[AutoEDPTOIL]

 

                                                                               ,[ConfigId]

 

                                          

 

              FROM [AllocateLink].[dbo].[TP_A7_TEMP_StaffConfig_Processed] T1

 

              WHERE NOT EXISTS (SELECT T2.[TeampayStaffID]

 

                                                  FROM [AllocateLink].[dbo].[TP_A7_StaffConfig_Processed] T2

 

                                              WHERE T1.[TeampayStaffID] = T2.[TeampayStaffID]

 

                                                  AND T1.[StartDate] = T2.[StartDate])

 

                                                  --AND T1.[EndDate] = T2.[EndDate])

 

                                                                                       

 

              ORDER BY T1.[TeampayStaffID], T1.[StartDate]

 

                     

                       

                      DELETE T1 FROM  AllocateLink.dbo.TP_A7_StaffConfig_Processed T1 JOIN TeamPay.dbo.StaffConfig T2 ON

                      T1.ConfigId=T2.ConfigID and T1.TeampayStaffID=t2.StaffID

                      WHERE T2.IsActive=0

 

 

                      DELETE FROM Allocate7.dbo.Staffconfig_Processed WHERE TeampaySCPID not in (select SCP_ID from AllocateLink.dbo.TP_A7_StaffConfig_Processed )

 

END

GO