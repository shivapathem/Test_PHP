USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_Leavers_Records]    Script Date: 06/01/2026 15:46:45 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- ============================================= 
-- Author:		<HCL>
-- Create date: <17-Mar-2022>
-- Description:	This SP is used to return all set of records which is incorrectly present in teampay and in Allocate7.
-- =============================================
CREATE OR ALTER      PROCEDURE [dbo].[usp_Get_Leavers_Records] 
	@schedulingTeamId	INT = 0,
	@empNumber			INT = 0,
	@estCode			VARCHAR(10) = '',
	@filterType			INT = 0,
	@name				VARCHAR(100) = '',
	@records			INT = 0,
	@recordOffset		INT = 0
AS
BEGIN

	SET NOCOUNT ON;
	DECLARE @offset INT = @recordOffset * 100
	DECLARE @queryConditions VARCHAR(MAX) = ''
	DECLARE @query VARCHAR(MAX) = ''
	IF(@filterType = 0)
	BEGIN
		if(@schedulingTeamId > 0)
		BEGIN	
			SET @queryConditions =	@queryConditions + ' AND schedulingTeamId != ' + convert(varchar, @schedulingTeamId);
		END
		if(@empNumber > 0)
		BEGIN	
			SET @queryConditions =	@queryConditions + ' AND TeampayEmpNumber != '+ convert(varchar, @empNumber);
		END
		if(@name != '')
		BEGIN	
            SET @queryConditions = @queryConditions + ' AND CONCAT(ISNULL(A7SD.PreferredForename, A7SD.Forename), '
														 +char(39)+' '+char(39)+' , A7SD.Surname) NOT LIKE '
														 +char(39)+'%' + @name +'%'+char(39);
		END
		if(@estCode != '')
		BEGIN	
			SET @queryConditions = @queryConditions + ' AND A7SCP.CostCode NOT LIKE '+char(39) + @estCode + '%'+char(39);
		END
		IF(@records = 2)
		BEGIN
			SET @queryConditions = @queryConditions + ' AND NOT (SCP.comments IS NOT NULL AND SCP.comments != '+char(39)+'Approved'+char(39)+')';
		END
		ELSE IF(@records = 3)
		BEGIN
			SET @queryConditions = @queryConditions + ' AND (SCP.comments IS NOT NULL AND SCP.comments != '+char(39)+'Approved'+char(39)+')';
		END
	END
	ELSE
	BEGIN
		IF(@schedulingTeamId > 0)
		BEGIN	
			SET @queryConditions = @queryConditions + ' AND schedulingTeamId = ' + convert(varchar, @schedulingTeamId);
		END
		IF(@empNumber > 0)
		BEGIN	
			SET @queryConditions = @queryConditions + ' AND TeampayEmpNumber = '+ convert(varchar, @empNumber);
		END
		IF(@name != '')
		BEGIN	
            SET @queryConditions = @queryConditions + ' AND CONCAT(ISNULL(A7SD.PreferredForename, A7SD.Forename),  '
													+ char(39)+' '+char(39)+' , A7SD.Surname) LIKE '
													+ char(39) + '%'+ @name +'%'+char(39);
		END
		IF(@estCode != '')
		BEGIN	
			SET @queryConditions = @queryConditions + ' AND A7SCP.CostCode LIKE '+char(39) + @estCode + '%'+char(39);
		END
		IF(@records = 2)
		BEGIN
			SET @queryConditions = @queryConditions + ' AND (SCP.comments IS NOT NULL AND SCP.comments != '+char(39)+'Approved'+char(39)+')';
		END
		ELSE IF(@records = 3)
		BEGIN
			SET @queryConditions = @queryConditions + ' AND NOT (SCP.comments IS NOT NULL AND SCP.comments != '+char(39)+'Approved'+char(39)+')';
		END
	END
	SET @query = '
   select DISTINCT A7SD.EmpNumber, 
                A7SD.Forename, 
                A7SD.PreferredForename, 
                A7SD.Surname, 
				ISNULL(st.schedulingteamname,'''') as schedulingTeamName, 
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
                SCP.PaymentTypeID PaymentTypeName,
                UC.UC_PaymentTypeID A7_PaymentTypeName,
                A7SD.NetLogin TP_NetLogin,
                UD.UD_NetLogin A7_NetLogin,
                SCP.Comments as comments,
				A7SD.LeaveDate
		 FROM  StaffConfig_Processed AS SCP (nolock)
		 INNER JOIN UserConfigs UC ON UC.UC_SCP_ID = SCP.SCP_ID
         LEFT JOIN UserDetails  UD ON UD.UD_StaffNumber= SCP.TeampayStaffNumber
         LEFT JOIN StaffDetails A7SD ON A7SD.StaffNumber=SCP.TeampayStaffNumber    		  			  
		 LEFT JOIN ScheduledPersonTeam_LINK SL on SL.ScheduledPersonID = UD.UD_UserID 
													AND SL.ishometeam = 1
													AND sl.scheduledtype = 1 
													and getdate() between sl.startdate and sl.enddate
		  LEFT JOIN schedulingTeams ST on ST.schedulingteamid = SL.teamid 
		  WHERE UC.UC_EndDate >= ''2023-04-01''
		  --  AND A7SD.LeaveDate <= DATEADD(wk,6,getdate()) 
			AND ''Integration: default posi'' IN ( isnull(SCP.JobTitle, ''''), isnull(UC.UC_JobTitle,''''))
			AND  (
					(SCP.EndDate <> UC.UC_EndDate) OR
					(SCP.JobTitle <> UC.UC_JobTitle) OR
					(SCP.EFT <> UC.UC_EFT) OR
					(SCP.PartTimeEDP <> UC.UC_PartTimeEDP) OR
					(SCP.CostCode <> UC.UC_CostCode) OR
					(SCP.AccGroup <> UC.UC_AccGroup) OR
					(SCP.ManualEDP <> UC.UC_ManualEDP) OR
					(SCP.PaymentTypeID <> UC.UC_PaymentTypeID) OR
					(UD.UD_NetLogin <> A7SD.NetLogin)
				 )
				' + @queryConditions + '
				order by SCP.TeampayStaffNumber, SCP.StartDate, SCP.EndDate'
				+ ' OFFSET ' 
				+ convert(varchar, @offset) 
				+ ' ROWS FETCH NEXT 100 ROWS ONLY'

	exec (@query);

END