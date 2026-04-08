Use[AllocateLink]
GO

CREATE or ALTER PROCEDURE [usp_import_TP_A7_TempStaffAccPeriod]
AS

BEGIN
	
	Declare @date [varchar](10)
	Declare @currentuser [varchar](100)

	Set @date='20220101'

	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SET DATEFIRST 6;

	DECLARE @startdate		DATETIME
	DECLARE @enddate		DATETIME
	DECLARE @tmpdate		DATETIME
	DECLARE @accperiodid	INT
	DECLARE @accperiod		INT
	DECLARE @systemid		INT
	DECLARE @mindate		VARCHAR(10)

	DECLARE @UpdateDateTime datetime	DECLARE @strUpdateDateTime varchar(35)
	DECLARE @TmpHistory varchar(4000)

	SET @UpdateDateTime = GETDATE()
	SET @strUpdateDateTime = CONVERT(VARCHAR, @UpdateDateTime, 106) + ' at ' + CONVERT(VARCHAR, @UpdateDateTime, 108)
	SET @TmpHistory = 'Created by ' + @currentuser + ' on ' + @strUpdateDateTime

	
	SELECT @mindate = CONVERT(VARCHAR(10), MIN(T2.dDateTime), 103)
	  FROM Allocate7.dbo.TimeDimension T2
	 --WHERE T2.dDateTime >= DATEADD(DAY, DATEDIFF(DAY, 0, GETDATE()), 0)
	 WHERE T2.dDateTime >= DATEADD(DAY, DATEDIFF(DAY, 0, GETDATE()-90), 0)
	   AND T2.ixDayInWeek = 6

	--IF @mindate IS NULL
	--	BEGIN
	--		--RETURN 0
	--	END

	
	IF CONVERT(DATETIME, @date + ' 00:00:00', 103) <= CONVERT(DATETIME, @mindate + ' 00:00:00', 103)
		BEGIN
			SET @date = CONVERT(VARCHAR(10), DATEADD(ww, 52, CONVERT(DATETIME, @mindate + ' 00:00:00', 103)), 103)
	
		--RETURN 0
		END

		
	TRUNCATE TABLE Allocatelink.dbo.TP_A7_TempStaffAccPeriod

	INSERT INTO Allocatelink.dbo.TP_A7_TempStaffAccPeriod (NetLogin, StaffID, StartWeek, EndWeek, StartDate, EndDate, Period, AccGroupID)
	SELECT  T10.NetLogin as NetLogin, 
					T10.StaffID, 
					T20.ixYearWeek as StartWeek, 
					T30.ixYearWeek as EndWeek, 
					T20.dDateTime as StartDate, 
					T30.dDateTime as EndDate, 
					T10.Period,
					T10.AccGroupID 
					
	  FROM ((((((((SELECT T1.Staffid, 
						  T2.AccGroupID, 
						  T2.StartDate, 
						  COALESCE(T2.EndDate, '99991231') AS EndDate, 
						  CAST(T3.[Period] AS REAL) as [Period],
						  T1.IsLeaver, 
						  T1.LeaveDateSat,
						  T1.NetLogin
			
			  --T2.SchedulingSystemID
			FROM (((Teampay.dbo.StaffDetails T1 JOIN AllocateLink.dbo.TP_A7_StaffDetails T9 ON T1.NetLogin=T9.NetLogin
					INNER JOIN Teampay.dbo.StaffConfig T2 ON T1.StaffID = T2.StaffID AND T2.IsActive = 1)
					INNER JOIN Teampay.dbo.AccountingGroups T3 on T2.AccGroupID = T3.ID)
					INNER JOIN Teampay.dbo.REF_PaymentType T0 ON T0.PaymentTypeID = T2.PaymentTypeID)
		   WHERE T2.startdate <= CONVERT(DATETIME, @date  + ' 00:00:00', 103)
			 AND T2.enddate >= CONVERT(DATETIME, @mindate + ' 00:00:00', 103)
			 --AND T1.StaffID = COALESCE(6037, T1.StaffID)
			 --AND T0.UnPaid = 0
		   ) AS T10
			INNER JOIN Teampay.dbo.TimeDimension T20 ON DATEDIFF(d, T10.StartDate, T20.dDateTime)/(T10.[Period]*7) = FLOOR(DATEDIFF(d, T10.StartDate, T20.dDateTime)/(T10.[Period]*7)) )
			INNER JOIN Teampay.dbo.TimeDimension T30 ON T30.dDateTime = DATEADD(d, (T10.[Period]*7)-1, T20.dDateTime))
			INNER JOIN Teampay.dbo.StaffContract T4 ON T10.StaffID = T4.StaffID AND T4.IsActive = 1 AND T4.DepartmentID <> 0)))))
			--INNER JOIN REF_ContractCode T5 ON T5.ContractCode = T4.ContractCode)
			--INNER JOIN REF_StaffType T6 ON T6.TypeID = T5.StaffType)
			--INNER JOIN [StaffPosition_SAT_SmallGapsRemoved] T11 ON T10.StaffID = T11.StaffID AND T11.IsActive = 1)
			--INNER JOIN [REF_EmpGroup] T7 ON T7.EmpGroupCode = T11.EmployeeGroup AND IsTeampayPayments = 1)
			
	 WHERE T20.dDateTime >= T10.StartDate
	   AND T30.dDateTime <= T10.EndDate
	   AND T30.dDateTime >= CONVERT(DATETIME, @mindate + ' 00:00:00', 103)
	   AND T30.dDateTime <= CONVERT(DATETIME, @date + ' 00:00:00', 103)
	   AND T20.dDateTime BETWEEN T4.StartDate AND COALESCE(T4.EndDate, '99991231') 
	   --AND T20.dDateTime BETWEEN DATEADD(DAY, 1-DATEPART(WEEKDAY, T11.StartDate),T11.StartDate) AND COALESCE(T11.EndDate, '99991231') 
	   AND T20.ixYearWeek >= ((((DATEPART(YEAR, GETDATE())) - 2) * 100) + 1) 
	   --AND (T10.IsLeaver = 0 OR (T10.IsLeaver = 1 AND T10.LeaveDateSat >= T30.dDateTime))
	   --AND T11.ActualPosition <> 99999999
	   --AND NOT EXISTS (SELECT AccPeriodID FROM TempStaffAccPeriod 
	   --				  WHERE Username = @currentuser AND Staffid = T10.staffid AND startdate = T20.dDateTime)
	 ORDER BY T10.StaffID, T20.dDateTime


END

GO

EXEC usp_import_TP_A7_TempStaffAccPeriod

GO

