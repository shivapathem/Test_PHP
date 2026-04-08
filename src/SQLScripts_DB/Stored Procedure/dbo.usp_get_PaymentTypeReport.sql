USE [Allocate7]

GO

 

/****** Object:  StoredProcedure [dbo].[usp_get_EFTReport]    Script Date: 04/10/2023 20:22:17 ******/

SET ANSI_NULLS ON

GO

 

SET QUOTED_IDENTIFIER ON

GO

 

 

CREATE   OR ALTER   PROCEDURE [dbo].[usp_get_EFTReport]

       @DepartmentID INT = NULL

AS

 

BEGIN

SET NOCOUNT ON;

DECLARE @i int = 0

       DECLARE       @StartDate Date

       SET @StartDate=GETDATE()-70

 

       CREATE TABLE #Dates (StartDate  Date)

 

       WHILE @i < 126

       BEGIN

              SET @i = @i + 1

      

                      INSERT INTO #Dates VALUES ( @StartDate )

 

              SET @StartDate=DATEADD(day,1, @StartDate)

       END

 

      

 

                             SELECT T1.Startdate,

                                           T2.StartDate as ContratcStartDate,

                                           T2.EndDate as ContractEndDate,

                                           T2.StaffID,

                                           FORMAT(T2.EFT,'N2') ContractEFT,

                                           T2.DepartmentID

                             INTO #EFTContract

                             FROM #Dates T1 JOIN Teampay.dbo.StaffContract T2 ON T1.StartDate between T2.StartDate and T2.EndDate

                             WHERE T2.IsActive=1 AND T2.DepartmentID>0

 

 

                             Select T1.Startdate,

                                           T2.StartDate as ConfigStartDate,

                                           T2.EndDate as ConfigEndDate,

                                           T2.StaffID,PaymentTypeName,

                                           IsTermTime,

                                           Period,

                                           PartTimeEDP,

                                           EDPMinimumExcBreaks,

                                           T5.AccGroup,

                             FORMAT(CASE WHEN isnull(T2.PartTimeEDP,0)>0 then ((T2.PartTimeEDP/Period))/35 else (T2.EDPMinimumExcBreaks/Period)/35 end , 'N2')ConfigEFT

                             INTO #EFTConfig

                             FROM #Dates T1 JOIN Teampay.dbo.StaffConfig T2 ON T1.StartDate BETWEEN T2.StartDate and T2.EndDate

                             JOIN Teampay.dbo.AccountingGroups T5 ON T5.id=T2.AccGroupID

                             JOIN Teampay.dbo.REF_PaymentType T6  ON T6.PaymentTypeID=T2.PaymentTypeID

                             WHERE T2.IsActive=1

 

                            

 

                             Select  DISTINCT

                                                  T1.StaffID,CONCAT( Surname, ' ', Forename) DisplayName,

                                                  CASE WHEN DepartmentID=1 THEN 'Technology Group'

                                                           WHEN DepartmentID=2 THEN 'News and Current Affairs' END Department,

                                                  MIN(t1.StartDate) Startdate,

                                                  MAX(t1.startdate) Endate,

                                                  PartTimeEDP,

                                                  EDPMinimumExcBreaks,

                                                  Period Week,

                                                  ContractEFT,

                                                  ConfigEFT,

                                                  CASE WHEN IsTermTime=0 THEN 'NO - Standard EDP Hours for Acc Period'

                                                           WHEN IsTermTime=1 THEN 'YES - Override EDP Standard Hours' END AS TermTimeHours,

                                                  CASE WHEN IsLeaver=0 THEN 'No' ELSE 'Yes' END IsLeaver

                            

                             FROM #EFTConfig T1 JOIN #EFTContract T2 ON T1.StaffID=T2.StaffID and T1.StartDate=T2.StartDate

                             JOIN Teampay.dbo.StaffDetails T3 ON T3.staffid=T1.Staffid

                             WHERE PaymentTypeName <>'UNPAID' and ContractEFT<>ConfigEFT

                             AND (IsTermTime<>1 AND   EDPMinimumExcBreaks<>999 AND (EDPMinimumExcBreaks<>53 and AccGroup<>'1A'))

                             AND DepartmentID=@DepartmentID

                             GROUP BY T1.StaffID,ContractEFT,ConfigEFT,T3.surname,T3.forename,DepartmentID,PreferredForename,PartTimeEDP,EDPMinimumExcBreaks,Period,IsTermTime,IsLeaver

                             ORDER BY DisplayName

 

                            

 

END

 

GO