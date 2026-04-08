USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_Incorrect_Records]    Script Date: 28/01/2026 19:39:23 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- ============================================= 
-- Author:           <HCL>
-- Create date: <17-Mar-2022>
-- Description:      This SP is used to return all set of records which is incorrectly present in teampay and in Allocate7.
-- =============================================
CREATE OR ALTER     PROCEDURE [dbo].[usp_Get_Incorrect_Records] 
       -- Add the parameters for the stored procedure here
       @schedulingTeamId    INT = 0,
       @empNumber                  INT = 0,
       @estCode                    VARCHAR(10) = '',
       @filterType                 INT = 0,
       @name                        VARCHAR(100) = '',
       @records                    INT = 0,
       @recordOffset        INT = 0
AS
BEGIN
       -- SET NOCOUNT ON added to prevent extra result sets from
       -- interfering with SELECT statements.
       SET NOCOUNT ON;
       DECLARE @offset INT = @recordOffset * 100
       DECLARE @queryConditions VARCHAR(MAX) = ''
       DECLARE @query VARCHAR(MAX) = ''
       IF(@filterType = 0)
       BEGIN
              if(@schedulingTeamId > 0)
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND schedulingTeamId != ' + convert(varchar, @schedulingTeamId);
              END
              if(@empNumber > 0)
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND TeampayEmpNumber != '+ convert(varchar, @empNumber);
              END
              if(@name != '')
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND CONCAT(ISNULL(TPSD.PreferredForename, TPSD.Forename), '+char(39)+' '+char(39)+' , TPSD.Surname) NOT LIKE '+char(39)+'%' + @name +'%'+char(39);
              END
              if(@estCode != '')
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND SCP.CostCode NOT LIKE '+char(39) + @estCode + '%'+char(39);
              END
              IF(@records = 2)
              BEGIN
                      SET @queryConditions =      @queryConditions + ' AND (comments IS NOT NULL AND comments = '+char(39)+'Approved'+char(39)+')';
              END
              ELSE IF(@records = 3)
              BEGIN
                      SET @queryConditions =      @queryConditions + ' AND comments != '+char(39)+'Approved'+char(39);
              END
       END
       ELSE
       BEGIN
              IF(@schedulingTeamId > 0)
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND schedulingTeamId = ' + convert(varchar, @schedulingTeamId);
              END
              IF(@empNumber > 0)
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND TeampayEmpNumber = '+ convert(varchar, @empNumber);
              END
              IF(@name != '')
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND CONCAT(ISNULL(TPSD.PreferredForename, TPSD.Forename),  '+char(39)+' '+char(39)+' , TPSD.Surname) LIKE '+ char(39) + '%'+ @name +'%'+char(39);
              END
              IF(@estCode != '')
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND SCP.CostCode LIKE '+char(39) + @estCode + '%'+char(39);
              END
              IF(@records = 2)
              BEGIN
                      SET @queryConditions =      @queryConditions + ' AND (comments IS NOT NULL AND comments != '+char(39)+'Approved'+char(39)+')';
              END
              ELSE IF(@records = 3)
              BEGIN
                      SET @queryConditions =      @queryConditions + ' AND (comments is null OR comments = '+char(39)+'Approved'+char(39)+')';
              END
       END
       SET @query = '
     select DISTINCT TPSD.EmpNumber, 
                TPSD.Forename, 
                TPSD.PreferredForename, 
                TPSD.Surname, 
                '''' as schedulingTeamName, 
                SCP.SCP_ID, 
                SCP.TeampayStaffID ,
                SCP.TeampayStaffNumber,     
                SCP.TeampayEmpNumber ,
                SCP.StartDate ,
                SCP.EndDate, 
                UC_EndDate A7_EndDate ,
                SCP.JobTitle, 
                UC_JobTitle A7_JobTitle, 
                SCP.Grade,
                SCP.Grade A7_Grade,
                SCP.EmpGroup EmpGroup ,
                SCP.EmpGroup A7_EmpGroupDescription ,
                SCP.EmpSubGroup, 
                SCP.EmpSubGroup A7_EmpSubGroupDescription, 
                SCP.EFT,
                UC_EFT A7_EFT,
                SCP.EDPMinimumExcBreaks, 
                SCP.EDPMinimumExcBreaks A7_EDPMinimumExcBreaks, 
                SCP.PartTimeEDP, 
                UC.UC_PartTimeEDP A7_PartTimeEDP, 
                SCP.CostCode ,
                UC.UC_CostCode A7_CostCode,
                SCP.AccGroup ,
                UC.UC_AccGroup A7_AccGroup ,
                SCP.ManualEDP ,
                UC.UC_ManualEDP A7_ManualEDP ,
                SCP.PaymentTypeID TP_PaymentType,
                UC.UC_PaymentTypeID A7_PaymentType,
                TPSD.NetLogin TP_NetLogin,
                A7SD.UD_NetLogin A7_NetLogin,
                SCP.Comments as comments
            FROM StaffConfig_Processed AS SCP (nolock)
      INNER JOIN UserConfigs UC on UC.UC_SCP_ID = SCP.SCP_ID
            LEFT JOIN UserDetails A7SD ON A7SD.UD_StaffNumber= SCP.TeampayStaffNumber
            LEFT JOIN StaffDetails TPSD ON TPSD.StaffNumber=SCP.TeampayStaffNumber      
            WHERE UC.UC_EndDate >=''2023-04-01''  
      AND ISNULL(SCP.JobTitle,'''') <>  ''Integration: default posi''
      AND ISNULL(UC_JobTitle,'''') <>  ''Integration: default posi''
      AND  (
              (SCP.EndDate <> UC.UC_EndDate) OR
              (SCP.JobTitle <> UC.UC_JobTitle) OR
              (SCP.EFT <> UC.UC_EFT) OR
              (SCP.PartTimeEDP <> UC.UC_PartTimeEDP) OR
              (SCP.CostCode <> UC.UC_CostCode) OR
              (SCP.AccGroup <> UC.UC_AccGroup) OR
              (SCP.ManualEDP <> UC.UC_ManualEDP) OR
              (SCP.PaymentTypeID <> UC.UC_PaymentTypeID) OR
              (A7SD.UD_NetLogin <> TPSD.NetLogin)
          )
      ' + @queryConditions + '
      order by SCP.TeampayStaffNumber, SCP.StartDate, SCP.EndDate OFFSET ' 
      + convert(varchar, @offset) 
      + ' ROWS FETCH NEXT 100 ROWS ONLY'

       EXEC (@query);
END