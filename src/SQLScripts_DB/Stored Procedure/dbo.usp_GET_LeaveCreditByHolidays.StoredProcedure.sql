USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_LeaveCreditByHolidays]    Script Date: 1/9/2026 10:41:52 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_GET_LeaveCreditByHolidays]  

@teamid INT,
@leaveyear INT,
@timedimensionid INT,
@isactive INT = 0   
AS 
DECLARE @leaveyearstartdate date,@leaveyearenddate date
	
	set @leaveyearstartdate = DATEFROMPARTS(@leaveyear,04,01)
	set @leaveyearenddate = DATEFROMPARTS(@leaveyear+1,03,31)

	if @isactive = 1 
	BEGIN
		set @leaveyearstartdate = GETDATE()
		set @leaveyearenddate = GETDATE()
	END

BEGIN

		Select UD_DisplayLastName + ', ' + UD_DisplayFirstName AS FullName,
			   UD_StaffNumber
			   StaffNumber,
			   UD_NetLogin
			   NetLogin,
			   spl.SortCode,
			   ISNULL(Cast(UD_PHLLeaveAmount AS VARCHAR(250)), 
					Cast('0.00' AS VARCHAR(250)))				AS  PHLLeaveAmount,
			   la.PHL
			   as LeaveCatAmount,
			   la.dDate,
			   td.ixDateKey,
			   ISNULL(td.ID, 0)
			   AS TDID,
			   td.sEvent,
			   UD_UserID
			   ScheduledPersonID,
			   UD_TeampayStaffID
			   StaffID
		FROM   UserDetails WITH (NOLOCK)
		JOIN ScheduledPersonTeam_LINK spl WITH (NOLOCK)  on spl.ScheduledPersonID = UD_UserID
					and spl.IsHomeTeam = 1
					and ISNULL(convert(datetime, EndDate, 110), '9999-01-01') >= Cast (
						@leaveyearstartdate AS DATE)
					and convert(datetime, StartDate, 110) <= Cast (
						@leaveyearenddate AS DATE)
		LEFT join LeaveAllocation la WITH (NOLOCK) on la.SchedulingPersonID = UD_UserID
						 and la.iYear = @leaveyear
						 AND la.IsActive = 1
						 AND la.TimeDemensionID = @timedimensionid
	    LEFT join TimeDimension td WITH (NOLOCK)  on td.ID = la.TimeDemensionID
		where  spl.TeamID = @teamid
		  AND UD_NetLogin IS NOT NULL
		ORDER  BY FullName 

END