USE [BBCSchedules]
GO
/****** Object:  UserDefinedFunction [dbo].[ufn_get_SchedulingTeamROTA]    Script Date: 28/07/2025 19:20:12 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Rajesh Kumbalai
-- Create date: 26-June-2024
-- Description:	This fuction return the time zone name based on the 
--              Date and Time Zone ID provided
-- =============================================
ALTER         FUNCTION [dbo].[ufn_get_SchedulingTeamROTA]
(

	@pweekNumber INT,
	@pteamId INT
)
RETURNS @TeamROTA TABLE  
            ( 
			   MasterDutyID INT,
			   DutyName VARCHAR(50),
		       Duration INT,
			   WeekNumber INT,
			   iDay INT,
			   StartTime INT,
			   EndTime INT,
			   BackColour NCHAR(10),
			   FontColour NCHAR(10),
			   SchedulingTeamID INT,
			   SchedulingPersonID INT,
			   DutyDate DATE,
			   StartDate DATETIME,
			   EndDate DATETIME,
			   SortCode NVARCHAR(50),
			   DutyProgramId INT,
			   DutyProgramId2 INT,
			   DutyProgramId3 INT,
			   DutyProgramId4 INT,
			   DutyProgramId5 INT,
			   DutyProgramId6 INT,
			   DutyBreakTime INT,
			   DutyColourID INT,
			   isHomeTeam BIT,             
			   IsNeedCovering BIT,
			   IsOverrideOver12 BIT,
			   DutyTypeID		INT
			 ) 
AS
BEGIN

        INSERT INTO @TeamROTA
		SELECT MasterDutyID,
			   DutyName,
		       Duration,
			   WeekNumber,
			   iDay,
			   StartTime,
			   EndTime,
			   BackColour,
			   FontColour,
			   SchedulingTeamID,
			   SchedulingPersonID,
			   DutyDate,
			   StartDate,
			   EndDate,
			   SortCode,
			   DutyProgramId,
			   DutyProgramId2,
			   DutyProgramId3,
			   DutyProgramId4,
			   DutyProgramId5,
			   DutyProgramId6,
			   DutyBreakTime,
			   DutyColorID,
			   isHomeTeam,             
			   IsNeedCovering,
			   IsOverrideOver12,
			   DutyTypeID
		  FROM 
		(
		  SELECT md.masterdutyid         AS masterdutyid,
		         md.dutyname           AS dutyname,
                 case when md.dutyname  <> 'U' and ISNULL(md.duration,0) = 0 then 
                   case when md.endtime > md.starttime then md.endtime- md.starttime
                    when md.endtime < md.starttime then (86400-md.starttime)+md.endtime end
                   else  md.duration end as duration,
				 td3.weeknumber        AS weeknumber,
				 td3.iday              AS iday,
				 case when MD.StartTime >= 86400 then ( MD.StartTime - 86400) else MD.StartTime end     AS starttime,
				 case when MD.EndTime >= 86400 then (MD.EndTime - 86400) else MD.EndTime end            AS endtime,
				 md.backcolour         AS backcolour,
				 MD.forecolour         AS fontcolour,
				 stl.teamid            AS SchedulingTeamId,
				 stl.scheduledpersonid AS SchedulingPersonID,
				 td3.dutydate,
				 CASE
					WHEN md.starttime IS NULL THEN NULL
					WHEN MD.StartTime = 0 AND MD.EndTime = 0 THEN NULL
					WHEN md.starttime = 0 AND md.EndTime > 0 THEN TD3.dutydate
					WHEN md.starttime > 0 THEN dbo.ufn_ConvertToDateTime(TD3.dutydate,md.starttime)
					ELSE dbo.ufn_ConvertToDateTime(TD3.dutydate,md.starttime) END AS StartDate,
				 CASE
					WHEN md.endtime IS  NULL THEN NULL
					WHEN MD.StartTime = 0 AND MD.EndTime = 0 THEN NULL
					WHEN MD.StartTime > 0 AND md.EndTime = 0 THEN DATEADD(DAY,1,TD3.dutydate)
					WHEN md.endtime = 86400 THEN DATEADD(DAY,1,TD3.dutydate)
					WHEN md.endtime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,TD3.dutydate),md.endtime-86400)
					WHEN MD.endtime < MD.StartTime THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,TD3.dutydate),md.endtime-86400)
					ELSE dbo.ufn_ConvertToDateTime(TD3.dutydate,md.EndTime) END AS EndDate,
				 ISNULL(STL.SortCode,'') as SortCode,
				 MD.DutyProgramId1       AS dutyprogramid,
				 MD.DutyProgramId2       AS dutyprogramid2,
				 MD.DutyProgramId3       AS dutyprogramid3,
				 MD.DutyProgramId4       AS dutyprogramid4,
				 MD.DutyProgramId5       AS dutyprogramid5,
				 MD.DutyProgramId6       AS dutyprogramid6,
				 md.breaktime            AS dutybreaktime,
				 md.dutycolourid         AS dutycolorid,
				 stl.ishometeam          AS ishometeam,   
				 md.dutytypeid,
				 isnull(md.IsNeedCovering,1)       AS IsNeedCovering,
				 isnull(md.IsOverrideOver12,1)     AS IsOverrideOver12,
				 Row_number() OVER( partition BY rp.scheduledpersonid, td3.weeknumber,td3.iday 
				 order by md.dutytypeid) as dutypriority
          FROM   RotaPeople AS rp
          INNER JOIN ScheduledPersonTeam_LINK AS stl
             ON rp.scheduledpersonid = stl.scheduledpersonid
              AND ( stl.ishometeam = 1
                 OR ( stl.ishometeam = 0
                    AND stl.IsAvailable = 1 ) )
          INNER JOIN MasterRotas AS mr ON mr.rotaid = rp.rotaid
          INNER JOIN RotaDuties AS rd (nolock) ON rd.rotaid = mr.rotaid
          INNER JOIN MasterDuties AS md
             ON md.masterdutyid = rd.masterdutyid
             AND ( @pweekNumber BETWEEN ISNULL(md.startweek,@pweekNumber) AND ISNULL(md.endweek,@pweekNumber ) )
          INNER JOIN (SELECT td.ixyearweek,
                  CASE
                  WHEN Row_number()
                       OVER(
                       partition BY schedulingpersonid
                       ORDER BY td.ixyearweek) % td.weeksinrota =
                     0 THEN
                  td.weeksinrota
                  ELSE Row_number()
                       OVER(
                       partition BY schedulingpersonid
                       ORDER BY td.ixyearweek) % td.weeksinrota
                  END weeksinrota,
                  schedulingpersonid
               FROM   (SELECT DISTINCT ixyearweek,
                           er.weeksinrota,
                           er.schedulingpersonid
                   FROM   TimeDimension td,
                      (SELECT DISTINCT mr.rotastartweek
                               AssignmentStartWeek,
                               mr.weeksinrota
                               WeeksInRota,
                               rp.scheduledpersonid AS
                               SchedulingPersonID
                       FROM   RotaPeople rp
                      INNER JOIN MasterRotas AS mr ON mr.rotaid = rp.rotaid
                                AND mr.teamid = @pteamId
                                AND rp.isactive = 1,
                         (SELECT Min(ddatetime) AS wStart_Date,
                             Max(ddatetime) AS wEnd_Date
                          FROM TimeDimension
                           WHERE ixyearweek = @pweekNumber) as TD5
                           WHERE isnull(rp.startdate,TD5.wend_date)  <= TD5.wend_date
                           AND isnull(rp.enddate,TD5.wstart_date)  >= TD5.wstart_date
                                ) er
                   WHERE  td.ixyearweek BETWEEN er.assignmentstartweek
                                AND
                                @pweekNumber) TD
              ) TD1 ON rd.rotaweek = td1.weeksinrota
                AND rp.scheduledpersonid = td1.schedulingpersonid,
             (SELECT Min(ddatetime) AS wStart_Date,
                 Max(ddatetime) AS wEnd_Date
            FROM   TimeDimension
            WHERE  ixyearweek = @pweekNumber) AS TD2,
             (SELECT ixyearweek  weeknumber,
                 ddatetime   dutydate,
                 ixdayinweek iday
            FROM   TimeDimension
            WHERE  ixyearweek = @pweekNumber) TD3
        WHERE  td1.ixyearweek = @pweekNumber
           AND stl.teamid = @pteamId
           AND rd.dotw = TD3.iday
           AND isnull(rd.startdate,td2.wend_date)  <= td2.wend_date
           AND isnull(rd.enddate,td2.wstart_date)  >= td2.wstart_date
           AND isnull(rp.startdate,td2.wend_date)  <= td2.wend_date
           AND isnull(rp.enddate,td2.wstart_date)  >= td2.wstart_date
           AND TD3.dutydate between stl.StartDate AND stl.EndDate
           AND stl.scheduledtype = 1
           AND rp.isactive = 1
           AND md.IsActive = 1
           AND rd.isactive = 1  
	) FD WHERE dutypriority = 1 

	RETURN;

 END