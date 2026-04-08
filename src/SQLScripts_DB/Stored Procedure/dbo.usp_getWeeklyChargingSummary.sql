USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_getWeeklyChargingSummary]    Script Date: 03/04/2026 15:46:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 31-05-2022
-- Description:	Used to get all data related to weekly charging summary
-- =============================================
CREATE OR ALTER       PROCEDURE [dbo].[usp_getWeeklyChargingSummary]
	@reportType VARCHAR(5),
	@chargeStatus VARCHAR(6),
	@sapDate date,
	@actualStatus VARCHAR(5),
	@weeksRange VARCHAR(5),
	@weeksStart VARCHAR(2),
	@toYear INT,
	@weekFinish VARCHAR(2),
	@fromYear INT,
	@schedulingTeam VARCHAR(50),
	@estabCodes VARCHAR(50),
	@receiverCode VARCHAR(50),
	@wbsCodes VARCHAR(50),
	@grpBy1 VARCHAR(5),
	@grpBy2 VARCHAR(15),
	@staffNumber VARCHAR(MAX),
	@sortingCol VARCHAR(63) = 'schedulingTeamName',
	@sortingType VARCHAR(7) = 'asc',
	@userID INT = NULL
AS
BEGIN
	DECLARE @queryCondition	VARCHAR(MAX) = ''
	DECLARE @queryMain	VARCHAR(MAX) = ''
	DECLARE @queryStr	VARCHAR(MAX) = ''
	DECLARE @queryGrpStr VARCHAR(MAX) = ''
	DECLARE @toDate date
	DECLARE @fromDate date
	SET NOCOUNT ON;
	IF(CAST(@weeksStart AS INT) < 10)
	BEGIN
		SET @weeksStart = CONCAT(0, CAST(@weeksStart AS INT));
	END
	IF(CAST(@weekFinish AS INT) < 10)
	BEGIN
		SET @weekFinish = CONCAT(0, CAST(@weekFinish AS INT));
	END
	IF(@reportType = 'GRP')
	BEGIN
		SET @queryStr = 'ST.schedulingTeamName, SUM(CDML.Quantity) Quantity, SUM(CDML.Quantity * CDML.UnitPrice) TotalPrice'
		SET @queryGrpStr = 'Group By ST.schedulingTeamName'
		IF(@grpBy2 = 'Name')
		BEGIN
			SET @queryStr = @queryStr + ', SP.UD_DisplayName'
			SET @queryGrpStr = @queryGrpStr + ', SP.UD_DisplayName'
		END
		IF(@grpBy2 = 'Estab')
		BEGIN
			SET @queryStr = @queryStr + ', EC.EstablishCode'
			SET @queryGrpStr = @queryGrpStr + ', EC.EstablishCode'
		END
		IF(@grpBy2 = 'Activity')
		BEGIN
			SET @queryStr = @queryStr + ', AC.ActivityCodeName'
			SET @queryGrpStr = @queryGrpStr + ', AC.ActivityCodeName'
		END
	END
	ELSE
	BEGIN
		SET @queryStr = 'DISTINCT CDML.ChargingId, ST.schedulingTeamName, EC.EstablishCode, CWC1.ChargeWbsCodeName ChargeCode, CDML.IsSentToFinance,
	CDML.IsActual, sp.UD_StaffNumber StaffNumber, ad.AD_DutyName as DutyName, CDML.Quantity, CDML.UnitPrice, (CDML.Quantity * CDML.UnitPrice) TotalPrice, SP.UD_DisplayName DisplayName, CDML.CreatedDate, CDML.Comments,
	CDML.SentToFinanceDate, CDML.ChargingDutyDate, CDML.ModifiedDate, TD.ixWeekInYear, TD.ixYear, TD.sDayName,
	AC.ActivityCodeName, SP1.UD_DisplayName CreatedBy'
	END
	IF(@chargeStatus = 'STSAP')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.IsSentToFinance = 1'
		IF(@sapDate != '')
		BEGIN
			SET @queryCondition = @queryCondition + ' AND convert(date, CDML.SentToFinanceDate) = convert(date, ' + char(39) + convert(varchar, @sapDate) + char(39) + ')'
		END
	END
	IF(@chargeStatus = 'NSTSAP')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.IsSentToFinance = 0'
	END
	IF(@actualStatus = 'Y')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.IsActual = 1'
	END
	IF(@actualStatus = 'N')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.IsActual = 0'
	END
	IF(@actualStatus = 'H')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.IsActual = 2'
	END
	IF(@weeksRange = 'SW')
	BEGIN
		SELECT @toDate = dDateTime from TimeDimension where ixDayInWeek = 0 AND ixYearWeek = CONVERT(INT, CONCAT(@toYear, @weeksStart))
		SELECT @fromDate = dDateTime from TimeDimension where ixDayInWeek = 6 AND ixYearWeek = CONVERT(INT, CONCAT(@toYear, @weeksStart))
		SET @queryCondition = @queryCondition + ' AND CDML.ChargingDutyDate BETWEEN CONVERT(date, ' + char(39) + convert(varchar, @toDate) + char(39) + ') AND CONVERT(date, ' + char(39) + convert(varchar, @fromDate) + char(39) + ')'
	END
	IF(@weeksRange = 'RoW')
	BEGIN
		SELECT @toDate = dDateTime from TimeDimension where ixDayInWeek = 0 AND ixYearWeek = CONVERT(INT, CONCAT(@toYear, @weeksStart))
		SELECT @fromDate = dDateTime from TimeDimension where ixDayInWeek = 6 AND ixYearWeek = CONVERT(INT, CONCAT(@fromYear, @weekFinish))
		SET @queryCondition = @queryCondition + ' AND CDML.ChargingDutyDate BETWEEN CONVERT(date, ' + char(39) + convert(varchar, @toDate) + char(39) + ') AND CONVERT(date, ' + char(39) + convert(varchar, @fromDate) + char(39) + ')'
	END
	IF(@staffNumber != '')
	BEGIN
		SET @staffNumber = REPLACE(@staffNumber, ',', ''', ''')
		SET @staffNumber = char(39) + @staffNumber  + char(39)
		SET @queryCondition = @queryCondition + ' AND AL.StaffNumber IN(' + @staffNumber + ')'
	END
	IF((@schedulingTeam != '') AND (@schedulingTeam != '-1'))
	BEGIN
		SET @queryCondition = @queryCondition + ' AND ST.SChedulingTeamId IN(' + @schedulingTeam + ')'
	END
	ELSE
	BEGIN
	   SET @queryCondition = @queryCondition + ' AND ST.SChedulingTeamId in ( select distinct st.SchedulingTeamId
															 FROM schedulingTeams st (nolock)
		WHERE 
		  EXISTS (
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				WHERE UR.UR_SchedulingTeamID = st.SchedulingTeamId
				AND UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND UR.UR_UserID = '+ cast(@userID as varchar) +' 
				AND RR.RoleName IN (''Advanced Reports'', ''Scheduling Team Admin'')
			UNION
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				INNER join schedulingTeams stf2 on stf2.divisionId = UR.UR_DivisionId
				WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND RR.RoleName = ''Area Admin''
				AND stf2.schedulingTeamId = st.schedulingTeamId
				AND UR.UR_UserID = '+ cast(@userID as varchar) +' 
			UNION
				SELECT 1
				FROM UserRoles UR
				INNER JOIN REF_Roles RR
				ON RR.RoleID = UR.UR_RoleID
				WHERE UR.UR_StartDate <= GETDATE() AND UR.UR_EndDate >= GETDATE()
				AND RR.RoleName = ''System Admin''
				AND UR.UR_UserID = '+ cast(@userID as varchar) +' 
			)  
		)'
	END
	IF(@estabCodes != '')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND EC.EstablishCode IN(' + char(39) + @estabCodes + char(39) + ')'
	END
	IF(@receiverCode != '')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.ChargeCodeId IN(' + @receiverCode + ')'
	END
	IF(@wbsCodes != '')
	BEGIN
		SET @queryCondition = @queryCondition + ' AND CDML.ChargeCodeId IN(' + @wbsCodes + ')'
	END
	IF(@sortingCol = '')
	BEGIN
		SET @sortingCol = 'schedulingTeamName';
	END
	IF(@sortingType = '')
	BEGIN
		SET @sortingType = 'ASC';
	END
	SET @queryMain = '
	SELECT ' + @queryStr + '
	FROM ChargingDutyMapping_Link CDML
	INNER JOIN ActivityCode AC ON CDML.ActivityCodeId = AC.ActivityCodeId
	INNER JOIN AllocationsScheduledPersons asp on asp.ASP_AllocationsSPID = CDML.AllocationId
	INNER join AllocationsDuties ad on ad.AD_AllocationsDutyID=asp.ASP_AllocationsDutyID
	INNER JOIN schedulingTeams ST ON ST.schedulingTeamId = ASP_ChargingTeamID AND ST.establishCodeID IS NOT NULL
	INNER JOIN EstablishCode EC ON EC.EstablishCodeId = ST.establishCodeID
	INNER JOIN ChargeWbsCode CWC1 ON CWC1.ChargeWbsCodeId = CDML.ChargeCodeId
	INNER join UserDetails sp on sp.UD_UserID=asp.ASP_SchedulingPersonID
	INNER JOIN TimeDimension TD ON convert(date, TD.dDateTime) = convert(date, CDML.ChargingDutyDate)
	INNER join UserDetails sp1 on sp1.UD_UserID=CDML.CreatedBy

	WHERE 1=1   ' + @queryCondition + ' '+ @queryGrpStr + ' ORDER BY ' + @sortingCol + ' ' + @sortingType

	exec (@queryMain)

END