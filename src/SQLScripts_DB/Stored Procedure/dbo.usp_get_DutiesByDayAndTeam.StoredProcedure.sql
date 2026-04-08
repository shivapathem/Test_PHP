USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_DutiesByDayAndTeam]    Script Date: 30/06/2022 11:50:39 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR  ALTER  PROCEDURE [dbo].[usp_get_DutiesByDayAndTeam]
@intWeek          INT,
@intDay           INT,
@intTeamID        INT,
@selectedDate     DATE,
@IsShiftleader    INT = NULL

AS
BEGIN
    -- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	
	DECLARE @SPList TABLE (AllocationId int, 
	                       DutyName NVARCHAR(100), 
						   SchedulingPersonID int,
						   FullName NVARCHAR(100),
						   IsHomeTeam INT,
						   ShowSP BIT,
						   StartTime NVARCHAR(100),
						   EndTime NVARCHAR(100)
						   )
	
	   IF ( ISNULL(@IsShiftleader,0) in (0,1) )
	    BEGIN
            
			INSERT INTO @SPList
			       (AllocationId,
				   DutyName,	   
				   SchedulingPersonID,
				   FullName,
				   IsHomeTeam,
				   ShowSP,
				   StartTime,
				   EndTime)
			SELECT AllocationId,
				   DutyName,	   
				   SchedulingPersonID,
				   FullName,
				   IsHomeTeam,
				   1,
				   StartTime,
				   EndTime
			FROM   (SELECT stl.scheduledpersonid AS SchedulingPersonID,
						   CASE
							 WHEN ( sp.displayname IS NULL ) THEN
							   CASE
								 WHEN ( sd.preferredforename IS NULL
										 OR sd.preferredforename = '' ) THEN (
								 sd.forename + '' + sd.surname )
								 ELSE ( sd.preferredforename + '' + sd.surname )
							   END
							 ELSE sp.displayname
						   END                       AS FullName,
					       ISNULL(al.dutyname,'U')   AS DutyName,
						   ISNULL(AL.id,0)           AS AllocationId,
						  right('0'+CAST( isnull(AL.StartTime,0) / 3600 AS varchar(2)),2) + ':' 
                          + right('0' + CAST( (isnull(AL.StartTime,0) % 3600)/60 AS varchar(2)),2) as StartTime,
						  right('0'+CAST( isnull(AL.EndTime,0) / 3600 AS varchar(2)),2) + ':' 
                          + right('0' + CAST( (isnull(AL.EndTime,0) % 3600)/60 AS varchar(2)),2) as EndTime,
						   CASE WHEN ( ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.schedulingteamid )
						         AND ISNULL(AL.Duration,0) <> 0
							    THEN 0 
								WHEN ISNULL(AL.MarkedOvertime,0) > 0
								THEN 0
								ELSE 1 END AS ShowPerson,
						  stl.ishometeam
					 FROM ScheduledPersonTeam_LINK AS stl
					INNER JOIN ScheduledPeople AS sp (nolock)  ON sp.scheduledpersonid = stl.scheduledpersonid
					 LEFT JOIN StaffDetails sd (nolock)  ON sd.staffid = sp.staffdetailsid
					 LEFT JOIN Allocations AL ON AL.schedulingpersonid=STL.scheduledpersonid  
					                         AND AL.weeknumber = @intWeek
											 AND AL.schedulingteamid = @intTeamID
											 AND AL.iday = @intDay 
					WHERE stl.teamid = @intTeamID
					  AND CONVERT(DATE,@selectedDate) >= Isnull(stl.startdate,  CONVERT(DATE,@selectedDate) )
					  AND CONVERT(DATE,@selectedDate) <= Isnull(stl.enddate,  CONVERT(DATE,@selectedDate) ) 					   
					  AND stl.scheduledtype = 1 and AL.StartTime is not null
					   UNION ALL
					  SELECT  0 as SchedulingPersonID,' ' AS FullName,a.DutyName   AS DutyName,
		   a.id AS AllocationID,
			right('0'+CAST( isnull(a.StartTime,0) / 3600 AS varchar(2)),2) + ':' 
                          + right('0' + CAST( (isnull(a.StartTime,0) % 3600)/60 AS varchar(2)),2) as StartTime,
			right('0'+CAST( isnull(a.EndTime,0) / 3600 AS varchar(2)),2) + ':' 
                          + right('0' + CAST( (isnull(a.EndTime,0) % 3600)/60 AS varchar(2)),2) as EndTime,
		   1 AS ShowPerson,1 AS ishometeam 
	  FROM [Allocations] as a (NOLOCK)
	 INNER JOIN Timedimension TD on A.WeekNumber=TD.ixYearWeek and A.iDay = TD.ixDayInWeek
	 INNER JOIN ( SELECT DENSE_RANK() over( order by td.dDateTime) as DayNumber,
		                    ixYearWeek WeekNumber,
							ixDayInWeek iDay
		              FROM Timedimension TD
					 WHERE TD.dDateTime between CONVERT(DATETIME,@selectedDate,101) 
					               and CONVERT(DATETIME,@selectedDate,101) ) TD1
					ON TD1.WeekNumber=TD.ixYearWeek AND TD1.iDay = TD.ixDayInWeek
	 WHERE  a.schedulingTeamId = @intTeamID
	   AND ISNULL(a.SchedulingPersonID,0) = 0
	   AND ISNULL(a.isActive,1) = 1  and a.StartTime is not null
	   AND TD.dDateTime between CONVERT(DATETIME,@selectedDate,101) and CONVERT(DATETIME,@selectedDate,101) 

			) SPList WHERE ShowPerson = 1 
			           AND NOT (     UPPER(DutyName) LIKE '%ABSENT%' 
			                     OR  UPPER(DutyName) LIKE '%SICK%' 
								 OR  UPPER(DutyName) LIKE '%LEAVE%')
	
	        UPDATE SP 
			   SET SP.ShowSP = 0
			  FROM @SPList SP
			 INNER JOIN ScheduledPersonTeam_LINK AS STL ON SP.SchedulingPersonID = STL.scheduledpersonid
			 INNER JOIN Allocations AL ON AL.schedulingpersonid=STL.scheduledpersonid  AND AL.schedulingteamid = STL.teamid
			 WHERE AL.weeknumber = @intWeek			   
			   AND AL.iday = @intDay 
			   AND ( ISNULL(AL.Duration,0) > 0
			             OR  UPPER(AL.DutyName) LIKE '%ABSENT%' 
			             OR  UPPER(AL.DutyName) LIKE '%SICK%' 
						 OR  UPPER(AL.DutyName) LIKE '%LEAVE%' )
			   AND CONVERT(DATE,@selectedDate) >= Isnull(stl.startdate,  CONVERT(DATE,@selectedDate) )
			   AND CONVERT(DATE,@selectedDate) <= Isnull(stl.enddate,  CONVERT(DATE,@selectedDate) ) 
			   AND STL.IsHometeam = 1
			   AND SP.IsHomeTeam=0 
			   AND SP.AllocationID = 0
			  
			SELECT AllocationId,
				   DutyName,	   
				   SchedulingPersonID,
				   FullName,
				   StartTime,
				   EndTime
			  FROM @SPList 
			  WHERE ShowSP=1 
			  ORDER BY FullName
	
		 END
	   ELSE
	     BEGIN

		  SELECT *
		  FROM (
					SELECT  CASE
							 WHEN al.id IS NULL THEN 0
							 ELSE al.id
						   END AS AllocationId,
						   CASE
							 WHEN al.dutyname IS NULL THEN spfull.dutyname
							 ELSE al.dutyname
						   END AS DutyName,	   
						   SPFull.scheduledpersonid  AS SchedulingPersonID,
						   SPFull.fullname AS FullName,cast (al.StartTime as varchar) as StartTime, cast (al.EndTime as varchar) as EndTime
					FROM   (SELECT stl.scheduledpersonid AS scheduledpersonid,
								   CASE
									 WHEN ( sp.displayname IS NULL ) THEN
									   CASE
										 WHEN ( sd.preferredforename IS NULL
												 OR sd.preferredforename = '' ) THEN (
										 sd.forename + '' + sd.surname )
										 ELSE ( sd.preferredforename + '' + sd.surname )
									   END
									 ELSE sp.displayname
								   END                   AS FullName,
								   'U'                   AS DutyName,
								   0                     AS ID
							FROM   ScheduledPersonTeam_LINK AS stl
								   INNER JOIN ScheduledPeople AS sp (nolock)
										   ON sp.scheduledpersonid = stl.scheduledpersonid
								   LEFT JOIN StaffDetails sd (nolock)
										  ON sd.staffid = sp.staffdetailsid
							WHERE  stl.teamid = @intTeamID
								   AND CONVERT(DATE,@selectedDate) >= Isnull(stl.startdate,  CONVERT(DATE,@selectedDate) )
								   AND CONVERT(DATE,@selectedDate) <= Isnull(stl.enddate,  CONVERT(DATE,@selectedDate) ) 
								   AND stl.scheduledtype = 1
								   --AND ISNULL(stl.isactive,1) = 1								   
								   AND ( stl.ishometeam = 1
										  OR ( stl.ishometeam = 0
											   AND stl.isavailable = 1 )
										   )) SPFull
				   LEFT JOIN (SELECT spl.scheduledpersonid AS schedulingpersonid,
									 CASE
									   WHEN ( sp.displayname IS NULL ) THEN
										 CASE
										   WHEN ( sd.preferredforename IS NULL
												   OR sd.preferredforename = '' ) THEN (
										   sd.forename + '' + sd.surname )
										   ELSE ( sd.preferredforename + '' + sd.surname )
										 END
									   ELSE sp.displayname
									 END                   AS FullName,
									 al.dutyname           AS DutyName,
									 AL.id                 AS ID,
									cast( AL.StartTime as varchar ) AS StartTime,
									 cast( AL.EndTime as varchar ) AS EndTime
							  FROM   Allocations AL
									 INNER JOIN ScheduledPeople AS sp (nolock)
											 ON sp.scheduledpersonid = AL.schedulingpersonid
									 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl
											 ON sp.scheduledpersonid = spl.scheduledpersonid
												AND spl.teamid = AL.schedulingteamid
									 LEFT JOIN StaffDetails sd (nolock)
											ON sd.staffid = sp.staffdetailsid
							  WHERE  AL.dutydate >= Isnull(spl.startdate, AL.dutydate)
									 AND AL.dutydate <= Isnull(spl.enddate, AL.dutydate)
									 AND ISNULL(spl.isactive,1) = 1
									 AND spl.scheduledtype = 1
									 AND AL.weeknumber = @intWeek
									 AND AL.schedulingteamid = @intTeamID
									 AND AL.iday = @intDay ) AL
						  ON SPFull.scheduledpersonid = AL.schedulingpersonid 	
				) SPList WHERE NOT (     UPPER(DutyName) like '%ABSENT%' 
			                     OR  UPPER(DutyName) like '%SICK%' 
								 OR  UPPER(DutyName) like '%LEAVE%')					   
		 
		  order by FullName Asc
		 END
		 
	
    
END
   