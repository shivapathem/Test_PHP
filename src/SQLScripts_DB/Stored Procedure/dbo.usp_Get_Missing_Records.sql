USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_Missing_Records]    Script Date: 28/01/2026 19:41:36 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- ============================================= exec usp_Get_Missing_Records 
-- Author:           <HCL>
-- Create date: <17-Mar-2022>
-- Description:      This SP is used to return all set of records which is present in teampay and not present in Allocate7.
-- =============================================
CREATE OR ALTER           PROCEDURE [dbo].[usp_Get_Missing_Records] 
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
                      SET @queryConditions = @queryConditions + ' AND CONCAT(PreferredForename, Forename, '+char(39)+' '
                                   +char(39)+' , Surname) NOT LIKE '+char(39)+'%' + @name +'%'+char(39);
              END
              if(@estCode != '')
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND dataContainer.CostCode NOT LIKE '+char(39) + @estCode + '%'+char(39);
              END
              IF(@records = 2)
              BEGIN
                      SET @queryConditions =      @queryConditions + ' AND (comments IS NOT NULL AND comments = '+char(39)+'Approved'+char(39)+')';
              END
              ELSE IF(@records = 3)
              BEGIN
                      SET @queryConditions =      @queryConditions + ' AND comments IS NOT NULL';
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
                      SET @queryConditions =      @queryConditions + ' AND CONCAT(PreferredForename, Forename, '+char(39)+' '+char(39)+' , Surname) LIKE '+ char(39) + '%'+ @name +'%'+char(39);
              END
              IF(@estCode != '')
              BEGIN  
                      SET @queryConditions =      @queryConditions + ' AND dataContainer.CostCode LIKE '+char(39) + @estCode + '%'+char(39);
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
              
              -- Get the list of missing records from AllocateLink

       SET @query = 'select DISTINCT SD.EmpNumber, 
                                        SD.StaffNumber, 
                                           SD.Forename, 
                                           SD.PreferredForename, 
                                           SD.Surname,  
                                           SCP.SCP_ID , 
                                           SCP.TeampayStaffID ,
                                           SCP.TeampayStaffNumber , 
                                           SCP.TeampayEmpNumber ,
                                           SCP.StartDate ,
                                           SCP.EndDate ,
                                           SCP.TeampayDepartmentID ,
                                           SCP.OrgID ,
                                           SCP.OrgPositionID ,
                                           SCP.JobTitle ,
                                           SCP.GradeID ,
                                           SCP.Grade,
                                           SCP.GradeConditions ,
                                           SCP.ActingGradeID ,
                                           SCP.ActingGrade ,
                                           SCP.ActingGradeConditions ,
                                           SCP.EmpGroupCode ,
                                           SCP.EmpGroup ,
                                           SCP.EmpSubGroupCode ,
                                           SCP.EmpSubGroup,
                                           SCP.PaymentTypeName,
                                           SCP.EFT ,
                                           SCP.IsPartTime ,
                                           SCP.CostCode ,
                                           SCP.ActivityType ,
                                           SCP.UPACode ,
                                           SCP.AccGroupID ,
                                           SCP.AccGroup ,
                                           SCP.EDPMinimum ,
                                           SCP.EDPMinimumExcBreaks ,
                                           SCP.PartTimeEDP, 
                                           SCP.AveDayLen ,
                                           SCP.AccDays ,
                                           SCP.TermsCondsVersionID ,
                                           SCP.PaymentTypeID ,
                                           SCP.BreaksGroupID ,
                                           SCP.ManualEDP ,
                                           SCP.CreatedDate ,
                                           SCP.LastModDate ,
                                           SCP.LastModBy, 
                                           SCP.History, 
                                           SCP.comments , 
                                           1 isNewData, 
                            isNull(UD.UD_UserID, 0) UserID, 
                            1 as RowNum,
              SD.StaffID
          INTO #TMP                                 
                    FROM StaffConfig_Processed AS SCP (nolock) 
					INNER JOIN StaffDetails AS SD (nolock) ON SCP.TeampayStaffNumber = SD.StaffNumber
                   LEFT JOIN UserDetails UD (nolock) ON UD.UD_StaffNumber = SD.StaffNumber
                   WHERE NOT EXISTS ( SELECT 1 
										FROM UserConfigs UC
                    WHERE UC.UC_SCP_ID = SCP.SCP_ID
                    AND UC.UC_StartDate > ''2023-04-01''
                   )
           AND SCP.StartDate > ''2023-04-01''


              -- Display latest 3 reocrds of staff for whom there is new config.
              -- e.g. Steve is already having 5 config into A7 and new config comes into AllocateLink.
              -- So system will display new config record along with latest 3 configs from A7.

              Select Distinct EmpNumber, 
              StaffNumber, 
                            Forename, 
                            PreferredForename, 
                            Surname,  
                            SCP_ID , 
                            TeampayStaffID ,
                            TeampayStaffNumber , 
                            TeampayEmpNumber ,
                            StartDate ,
                            EndDate ,
                            TeampayDepartmentID ,
                            OrgID ,
                            OrgPositionID ,
                            JobTitle ,
                            GradeID ,
                            Grade,
                            GradeConditions ,
                            ActingGradeID ,
                            ActingGrade ,
                            ActingGradeConditions ,
                            EmpGroupCode ,
                            EmpGroup ,
                            EmpSubGroupCode ,
                            EmpSubGroup,
                            PaymentTypeName,
                            EFT,
                            IsPartTime ,
                            CostCode ,
                            ActivityType ,
                            UPACode ,
                            AccGroupID ,
                            AccGroup ,
                            EDPMinimum ,
                            EDPMinimumExcBreaks ,
                            PartTimeEDP, 
                            AveDayLen ,
                            AccDays ,
                            TermsCondsVersionID ,
                            PaymentTypeID ,
                            BreaksGroupID ,
                            ManualEDP ,
                            CreatedDate ,
                            LastModDate ,
                            LastModBy, 
                            History, 
                            comments , 
                            isNewData, 
                            UserID, 
                            RowNum 
         from
              (
                      SELECT * FROM #TMP
                             UNION 
                     select DISTINCT SD.EmpNumber, 
                            SD.StaffNumber, 
                            SD.Forename, 
                            SD.PreferredForename, 
                            SD.Surname, 
                            SCP.SCP_ID , 
                            SCP.TeampayStaffID ,
                            SCP.TeampayStaffNumber , 
                            SCP.TeampayEmpNumber ,
                            SCP.StartDate ,
                            SCP.EndDate ,
                            SCP.TeampayDepartmentID ,
                            SCP.OrgID ,
                            SCP.OrgPositionID ,
                            SCP.JobTitle ,
                            SCP.GradeID ,
                            SCP.Grade,
                            SCP.GradeConditions ,
                            SCP.ActingGradeID ,
                            SCP.ActingGrade ,
                            SCP.ActingGradeConditions ,
                            SCP.EmpGroupCode ,
                            SCP.EmpGroup ,
                            SCP.EmpSubGroupCode ,
                            SCP.EmpSubGroup, 
                            SCP.PaymentTypeName,
                            SCP.EFT ,
                            SCP.IsPartTime ,
                            SCP.CostCode ,
                            SCP.ActivityType ,
                            SCP.UPACode ,
                            SCP.AccGroupID ,
                            SCP.AccGroup ,
                            SCP.EDPMinimum ,
                            SCP.EDPMinimumExcBreaks ,
                            SCP.PartTimeEDP, 
                            SCP.AveDayLen ,
                            SCP.AccDays ,
                            SCP.TermsCondsVersionID ,
                            SCP.PaymentTypeID ,
                            SCP.BreaksGroupID ,
                            SCP.ManualEDP ,
                            SCP.CreatedDate ,
                            SCP.LastModDate ,
                            SCP.LastModBy, 
                            SCP.History, 
                            SCP.comments , 
                            0 isNewData, 
                            isNull(UD.UD_UserID, 0) UserID, 
                            ROW_NUMBER() OVER (PARTITION BY SD.StaffNumber Order by SD.StaffNumber, SCP.StartDate  DESC) AS RowNum,
							SD.StaffID
                      FROM StaffConfig_Processed AS SCP (nolock)
					  INNER JOIN UserConfigs UC ON UC.UC_SCP_ID = SCP.SCP_ID
                      LEFT JOIN StaffDetails SD (nolock) ON SD.StaffNumber = SCP.TeampayStaffNumber
					  LEFT JOIN UserDetails UD (nolock) ON UD.UD_NetLogin = SD.NetLogin
                      WHERE EXISTS ( SELECT 1 FROM #TMP TP WHERE TP.StaffNumber = SCP.TeampayStaffNumber )
              ) dataContainer 
        WHERE RowNum <= 3 ' 
        + @queryConditions 
        + ' order by TeampayStaffNumber, StartDate, EndDate desc OFFSET ' 
        + convert(varchar, @offset) 
              + ' ROWS FETCH NEXT 100 ROWS ONLY'


       exec (@query);
END