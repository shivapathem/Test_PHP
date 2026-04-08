USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsEditWeekly]    Script Date: 25/05/2023 17:10:32 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE  [dbo].[usp_get_ReadAllocationsEditWeekly]
@startDate                 VARCHAR(22),
@EndDate                   VARCHAR(22),
@pteamId			       INT,
@filterCond                VARCHAR(MAX) = NULL,
@filterOrderCond           VARCHAR(MAX) = NULL


AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

    DECLARE @FilterSQL          VARCHAR(MAX)
	DECLARE @VFilter            VARCHAR(MAX)
	DECLARE @VLJFilter          VARCHAR(MAX)	
    DECLARE @pWeekNumber        INT
	DECLARE @FilterSetFlag      INT = 0
	DECLARE @vleftjoinflag      INT = 0
	DECLARE @vWeekNumber        INT = 0
	DECLARE @vStartWeek         INT = 0
	DECLARE @vEndWeek           INT = 0
	DECLARE @vminwtdweek        DATETIME
	DECLARE @vmaxwtdweek        DATETIME
	
	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1
	
	SELECT @pWeekNumber = td.ixyearweek
  	  FROM TimeDimension TD
     WHERE TD.dDateTime = @startDate 
	 
	 SELECT AL.* INTO #TempAllocations
	   FROM Allocations AL
	  INNER join Timedimension TD on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
	  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
	    AND AL.SchedulingTeamId = @pteamId
		
	SELECT distinct sap.staffid, AL.schedulingpersonid,AL.SchedulingTeamId,
		   sap.startdate, sap.enddate, sap.startweek, sap.endweek,
		   TD.ixYearWeek weeknumber, 0 as totalweeks, 0 as currentweek, 0 as totalduration
		   into #TempAccounting
	  FROM allocations AL
	 INNER JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid
	 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON sp.scheduledpersonid = spl.scheduledpersonid
								  AND spl.teamid=AL.SchedulingTeamId
	 INNER JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
	 INNER JOIN StaffAccPeriod SAP (nolock) ON sd.staffid = SAP.staffid
	 INNER join Timedimension TD on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
	 WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
	   AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
	   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
	   AND CONVERT(DATETIME,@startDate,101) <=  isnull( SAP.enddate, CONVERT(DATETIME,@startDate,101) ) 
	   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( SAP.startdate, CONVERT(DATETIME,@EndDate,101))	
	   AND AL.SchedulingTeamId = @pteamId	

	update #TempAccounting 
	set totalweeks= (select count(1)/7 from TimeDimension where ixYearWeek between startweek and endweek),
	currentweek=(select count(1)/7 from TimeDimension where ixYearWeek between startweek and weeknumber) 

	 update TC
		set TC.totalduration = AC.totaldtn
	   from #TempAccounting TC
	  inner join 
	  (
	 select TA.schedulingpersonid,
		    ta.weeknumber, 
		    sum( case when isnull(markwiad,0)=1 then 0 
			   else isnull(al.duration,0)-isnull(al.dutyBreakTime,0) end) as totaldtn
	   from #TempAccounting TA
	  inner join Allocations AL on TA.schedulingpersonid = AL.schedulingpersonid
	    AND TA.SchedulingTeamId = AL.SchedulingTeamId 
	  INNER JOIN TimeDimension TD on AL.WeekNumber = TD.ixYearWeek and AL.iday=TD.ixDayInWeek
	  WHERE td.dDateTime between ta.startdate and ta.enddate
	  group by TA.schedulingpersonid,ta.weeknumber
	  ) AC ON TC.schedulingpersonid = AC.schedulingpersonid and TC.WeekNumber = AC.Weeknumber	
		
	 select CL.AllocationId, CL.MasterDutyId,
			case
			when sum(case when CL.IsActual = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Green'
			when sum(case when CL.IsActual = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 'Red'
			when sum(case when CL.IsActual = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Blue'
			else 'None' end as TriangleColour 
			INTO #TempCharging
	   from ChargingDutyMapping_Link CL
	  INNER JOIN #TempAllocations AL ON AL.ID = CL.AllocationId AND AL.MasterDutyId = CL.MasterDutyId
	  GROUP by CL.AllocationId, CL.MasterDutyId		
	 
	 SELECT Al.schedulingpersonid,
	        AL.weeknumber,
			AL.duration,
			AL.starttime,
			AL.endtime,
			AL.dutydate,
			AL.iDay,
			AL.DutyName
	   INTO #TempAllocationsWTD
	   FROM Allocations AL
	   INNER JOIN (  SELECT TD.ixYearWeek weeknumber,
  			                td.ixDayInWeek iday
				 FROM TimeDimension TD
	            WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) - 6
				  AND CONVERT(DATETIME,@EndDate,101) + 6 ) TD 
      ON AL.WeekNumber = TD.weeknumber and AL.iDay = TD.iday
	    AND AL.SchedulingTeamId = @pteamId
        AND ISNULL(AL.schedulingpersonid,0) <> 0
		AND AL.DutyName <> 'U'			
				
	SELECT @pteamId as SchedulingTeamId,
	       fd.SchedulingPersonID as SchedulingPersonID, 
		   4 as breachtype,
	       fd.startdate as startdate,
		   fd.enddate as enddate, 
           'Allocation' as BreachedBy,
           getdate() as BreachedDate,
		   0 as IsApproved,
		   'Auto WTD Breach Verification Done at '+ CONVERT(VARCHAR, Getdate(), 108)
						   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History
	  INTO #AllocationBreachData
	  FROM (
			SELECT et.SchedulingPersonID,
				   min(et.dutydate) as startdate, 
				   max(et.dutydate) as Enddate,
				   DATEDIFF(day, min(et.dutydate) , max(et.dutydate)) as NoOFDays,
				   count(et.SchedulingPersonID) as DutyCount
			FROM #TempAllocationsWTD ST
			INNER JOIN  #TempAllocationsWTD ET ON ST.SchedulingPersonID = ET.SchedulingPersonID
			and ST.DutyDate between ET.DutyDate-6 and ET.DutyDate
			and upper(ST.Dutyname) not in ('-SICK','U-SICK','SICK','LEAVE','ABSENT','OFF LEAVE','U')
			and upper(ET.Dutyname) not in ('-SICK','U-SICK','SICK','LEAVE','ABSENT','OFF LEAVE','U')
			group by et.SchedulingPersonID, st.dutydate
	      ) FD where NoOFDays = 6 and DutyCount > 6	  
		  
		  
    INSERT INTO #AllocationBreachData
     select @pteamId as SchedulingTeamId,
	       fd.SchedulingPersonID as SchedulingPersonID, 
		   1 as breachtype,
	       cast(fd.startdate as date) as startdate,
		   cast(fd.enddate as date) as enddate, 
           'Allocation' as BreachedBy,
           getdate() as BreachedDate,
		   0 as IsApproved,
		   'Auto WTD Breach Verification Done at '+ CONVERT(VARCHAR, Getdate(), 108)
						   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History		   
	from (
	select cd.SchedulingPersonID,
		   pd.DutyDate as StartDate,
		   cd.DutyDate as EndDate, 
           Concat(Format (CASE WHEN pd.endtime < pd.StartTime THEN
										pd.DutyDate + 1
										 ELSE pd.DutyDate END,
                                                           'yyyy-MM-dd'), ' ', (
                                                    RIGHT('0' + Cast(Cast(
                                                    pd.EndTime AS
                                                    INT) / 3600
                                                    AS
                                                    VARCHAR), 2)
                                                    + ':'
                                                    + RIGHT('0' + Cast((Cast(
                                                    pd.EndTime AS
                                                    INT) / 60)
                                                    % 60 AS
                                                    VARCHAR),
                                                    2)
                                                    + ':'
                                                    + RIGHT('0' + Cast(Cast(
                                                    pd.EndTime AS
                                                    INT) % 60
                                                    AS
                                                    VARCHAR), 2)
                                                    + '.000' )) AS EndTime,
           Concat(Format (cd.DutyDate, 'yyyy-MM-dd'),
                                         ' ', (
                                         RIGHT('0' + Cast(Cast(cd.starttime AS INT)
                                         / 3600
                                         AS VARCHAR
                                         ), 2)
                                         + ':'
                                         + RIGHT('0' + Cast((Cast(cd.starttime AS
                                         INT) /
                                         60) % 60 AS
                                         VARCHAR)
                                         , 2)
                                         + ':'
                                         + RIGHT('0' + Cast(Cast(cd.starttime AS
                                         INT) % 60
                                         AS VARCHAR
                                         ), 2)
                                         + '.000' )) AS StartTime

		      from #TempAllocationsWTD  CD 
			inner join #TempAllocationsWTD PD on cd.SchedulingPersonID = pd.SchedulingPersonID
			and pd.DutyDate = cd.DutyDate -1 
						and upper(CD.Dutyname) not in ('-SICK','U-SICK','SICK','LEAVE','ABSENT','U','OFF LEAVE')
						and upper(PD.Dutyname) not in ('-SICK','U-SICK','SICK','LEAVE','ABSENT','U','OFF LEAVE')
			and ( isnull(pd.StartTime,0) > 0 or isnull(pd.EndTime,0) > 0 )
			and ( isnull(cd.StartTime,0) > 0 or isnull(cd.EndTime,0) > 0 )
			and cd.DutyDate  between CONVERT(DATETIME,@startdate,101) 
							  AND CONVERT(DATETIME,@EndDate,101) + 1 
			) FD where        Datediff(hour, CONVERT(DATETIME, EndTime),
									  CONVERT(DATETIME, StartTime))  <= 11
									  
 	SELECT AL.schedulingpersonid,
	       AL.weeknumber,
           min(AL.dutydate) AS StartDate,
		   max(AL.dutydate) AS EndDate,
		   Sum(Isnull(duration, 0.1)) / ( 3600 )  weektotal
		   INTO #AllocationBreachDataTemp
	FROM   dbo.allocations AL
		   INNER JOIN (SELECT Min (startweek) AS startweek,
							  Max(endweek)    endweek
					   FROM   (SELECT DISTINCT td.ixyearweek,
											   (SELECT TOP 1 *
												FROM
							  (SELECT DISTINCT TOP 11 td1.ixyearweek
							   FROM   timedimension TD1
							   WHERE  td1.ixyearweek < ( td.ixyearweek )
							   ORDER  BY TD1.ixyearweek DESC) td2 order by 1) AS
											   startweek,
											   (SELECT TOP 1 *
												FROM
							  (SELECT DISTINCT TOP 6 TD3.ixyearweek
							   FROM   timedimension TD3
							   WHERE  td3.ixyearweek >= ( td.ixyearweek )
							   ORDER  BY TD3.ixyearweek) td4
												ORDER  BY TD4.ixyearweek DESC)
											   AS endweek
							   FROM   timedimension TD
							   WHERE  ddatetime BETWEEN CONVERT(DATETIME, @startDate
														, 101)
														AND
														CONVERT(DATETIME, @EndDate,
														101)) td) td4
				   ON al.weeknumber BETWEEN td4.startweek AND td4.endweek
	WHERE  AL.schedulingteamid = @PTeamID
		   AND Isnull(AL.schedulingpersonid, 0) <> 0
		   AND ( dutyname <> 'U' )
	GROUP  BY AL.schedulingpersonid, AL.weeknumber	

    BEGIN

		DECLARE CUR_Breach CURSOR FOR 
		SELECT DISTINCT td.ixyearweek,
						(SELECT TOP 1 *
						 FROM   (SELECT DISTINCT TOP 11 td1.ixyearweek
								 FROM   timedimension TD1
								 WHERE  td1.ixyearweek < ( td.ixyearweek )
								 ORDER  BY TD1.ixyearweek DESC) td2 order by 1) AS startweek,
						(SELECT TOP 1 *
						 FROM   (SELECT DISTINCT TOP 6 TD3.ixyearweek
								 FROM   timedimension TD3
								 WHERE  td3.ixyearweek >= ( td.ixyearweek )
								 ORDER  BY TD3.ixyearweek) td4
						 ORDER  BY TD4.ixyearweek DESC)              AS endweek
		FROM   timedimension TD
		WHERE  ddatetime BETWEEN CONVERT(DATETIME, @startDate, 101) AND CONVERT(DATETIME, @EndDate, 101) 

		OPEN CUR_Breach

		FETCH NEXT FROM CUR_Breach INTO
		 @vWeekNumber,
		 @vStartWeek,
		 @vEndWeek
		
		WHILE @@FETCH_STATUS = 0
		  BEGIN
		    
			INSERT INTO #AllocationBreachData
			SELECT @pteamId as SchedulingTeamId,
				   fd.schedulingpersonid as SchedulingPersonID, 
				   3 as breachtype,
				   fd.StartDate as StartDate ,
				   fd.EndDate as EndDate, 
				   'Allocation' as BreachedBy,
				   getdate() as BreachedDate,
				   0 as IsApproved,
				   'Auto WTD Breach Verification Done at '+ CONVERT(VARCHAR, Getdate(), 108)
								   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History	
             FROM (
		    SELECT schedulingpersonid,
			       min(StartDate) as StartDate,
                   max(EndDate) as EndDate,
                   Round(Sum(weektotal) / ( 17 ), 0) AS weekavg
			  FROM #AllocationBreachDataTemp
			 WHERE weeknumber between @vStartWeek and @vEndWeek	
             GROUP BY schedulingpersonid
			 ) FD where fd.weekavg > 48
				   
			FETCH NEXT FROM CUR_Breach INTO 
			 @vWeekNumber,
			 @vStartWeek,
			 @vEndWeek
		  
		  END
		  
		CLOSE CUR_Breach;

		DEALLOCATE CUR_Breach;  
		
    END	
		 
	DROP TABLE IF EXISTS #AllocationBreachDataTemp
	
		INSERT INTO working_time_directive( SchedulingTeamId,
			   SchedulingPersonID, BreachType, StartDate,EndDate,
			   BreachedBy,BreachedDate,IsApproved,History )
		SELECT SchedulingTeamId,SchedulingPersonID,breachtype,StartDate,
			   EndDate,BreachedBy,BreachedDate,IsApproved,history
		 FROM #AllocationBreachData AD
		 WHERE NOT EXISTS (
						   SELECT 1 
							 FROM working_time_directive WD
							WHERE WD.SchedulingTeamId = AD.SchedulingTeamId
							  AND WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate)	;	

		WITH DBD (ID)
		AS
		(SELECT ID
		FROM working_time_directive WD
		where WD.SchedulingTeamId = @pteamid
		  and WD.startdate >= CONVERT(DATETIME,@startDate,101) -1
		  AND WD.EndDate <= CONVERT(DATETIME,@enddate,101) + 1 
		  AND WD.breachtype = 1
		  AND WD.IsApproved = 0
		  and not exists ( SELECT 1 
							 FROM #AllocationBreachData AD
							WHERE WD.SchedulingTeamId = AD.SchedulingTeamId
							  AND WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate)
			)
			DELETE from DBD	;	

		WITH WBD (ID)
		AS
		(SELECT ID
		FROM working_time_directive WD
		where WD.SchedulingTeamId = @pteamID
		  and WD.startdate >= CONVERT(DATETIME,@startDate,101) - 6
		  AND WD.EndDate <= CONVERT(DATETIME,@enddate,101) + 6 
		  AND WD.breachtype = 4
		  AND WD.IsApproved = 0
		  and not exists ( SELECT 1 
							 FROM #AllocationBreachData AD
							WHERE WD.SchedulingTeamId = AD.SchedulingTeamId
							  AND WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate)
			)
			DELETE from WBD;
			
			select @vminwtdweek = min(ddatetime), 
			       @vmaxwtdweek = max(ddatetime)
				from TimeDimension TD,
				(		SELECT DISTINCT td.ixyearweek,
									(SELECT TOP 1 *
									 FROM   (SELECT DISTINCT TOP 11 td1.ixyearweek
											 FROM   timedimension TD1
											 WHERE  td1.ixyearweek < ( td.ixyearweek )
											 ORDER  BY TD1.ixyearweek DESC) td2 order by 1) AS startweek,
									(SELECT TOP 1 *
									 FROM   (SELECT DISTINCT TOP 6 TD3.ixyearweek
											 FROM   timedimension TD3
											 WHERE  td3.ixyearweek >= ( td.ixyearweek )
											 ORDER  BY TD3.ixyearweek) td4
									 ORDER  BY TD4.ixyearweek DESC)              AS endweek
					FROM   timedimension TD
					WHERE  ddatetime BETWEEN CONVERT(DATETIME,@startdate, 101) AND CONVERT(DATETIME, @EndDate, 101) 
				) TD1 where TD.ixYearWeek between TD1.startweek and TD1.endweek	;	

		WITH SWBD (ID)
		AS
		(SELECT ID
		FROM working_time_directive WD
		where SchedulingTeamId = @pteamid
		  and startdate >= @vminwtdweek
		  AND EndDate <= @vmaxwtdweek
		  AND breachtype = 3
		  AND IsApproved = 0
		  and not exists ( SELECT 1 
							 FROM #AllocationBreachData AD
							WHERE WD.SchedulingTeamId = AD.SchedulingTeamId
							  AND WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate)
			)
			DELETE from SWBD;			
	
	
		SELECT al.allocateinstanceid              AS AllocateInstanceID,
			   al.departmentid                    AS DepartmentID,
			   al.allocationid                    AS AllocationID,
			   al.staffnumber                     AS StaffNumber,
			   al.dutyname                        AS DutyName,
			   al.duration                        AS Duration,
			   al.weeknumber                      AS WeekNumber,
			   al.iday                            AS iDay,
			   al.starttime                       AS StartTime,
			   al.endtime                         AS EndTime,
			   al.actinggrade                     AS ActingGrade,
			   al.sortcode                        AS SortCode,
			   al.leaveid                         AS LeaveID,
			   al.manualerr                       AS ManualERR,
			   al.dutycomments                    AS DutyComments,
			   al.bASecode                        AS BSeCode,
			   al.backcolour                      AS BackColour,
			   al.fontcolour                      AS FontColour,
			   al.personcomments                  AS PersonComments,
			   al.adhocduty                       AS AdhocDuty,
			   al.markedovertime                  AS MarkedOvertime,
			   al.markedptextraday                AS MarkedPTExtraDay,
			   al.markedcompleave                 AS MarkedCompLeave,
			   al.markedsickness                  AS MarkedSickness,
			   al.manualotamount                  AS ManualOTAmount,
			   al.manualotexcbreaksamount         AS ManualOTExcBreaksAmount,
			   al.unallocated                     AS UnAllocated,
			   al.id                              AS ID,
			   al.schedulingteamid                AS SchedulingTeamId,
			   al.schedulingpersonid              AS SchedulingPersonID,
			   FORMAT(al.dutydate, 'yyyy-MM-dd')  AS DutyDate,
			   al.dutydate                        AS DutyDateTime,
			   al.startdate                       AS StartDate,
			   al.enddate                         AS EndDate,
			   al.ispublished                     AS isPublished,
			   al.ishometeam                      AS IsHomeTeam,
			   al.markwiad                        AS MarkWiad,
			   al.markactual                      AS MarkActual,
			   al.aftermidnight                   AS aftermidnight,
			   al.isattention                     AS isAttention,
			   al.isrequest                       AS isRequest,
			   al.dutyprogramid                   AS dutyProgramId,
			   al.dutybreaktime                   AS dutyBreakTime,
			   al.dutycolorid                     AS dutyColorId,
			   al.mannualothours                  AS MannualOThours,
			   al.isedited                        AS isEdited,
			   al.mASterdutyid                    AS MasterDutyId,
			   al.isactive                        AS isActive,
			   al.isactiveduty                    AS isActiveDuty,
			   al.iseditable                      AS isEditable,
			   al.origallocationid                AS OrigAllocationID,
			   al.markwtd                         AS MarkWTD,
			   al.wtdcomments                     AS WTDComments,
			   al.iscompareedited                 AS isCompareEdited,
			   al.paymenttypename                 AS PaymentTypeName,
			   al.forename                        AS Forename,
			   al.surname                         AS Surname,
			   al.preferredforename               AS PreferredForename,
			   al.displayname                     AS DisplayName,
			   al.eft                             AS EFT,
			   al.acc                             AS ACC,
			   al.contractedhours                 AS ContractedHours,
			   al.accdays                         AS AccDays,
			   al.manualedp                       AS ManualEDP,
			   al.signin                          AS signin,
			   al.inbuilding                      AS inbuilding,
               al.ActionNameForSignin             AS ActionNameForSignin,
			   al.ImageNameSignin                 AS ImageNameSignin,			   
			   al.signinstarttime                 AS SignInStartTime,
			   al.signinendtime                   AS SignInEndTime,
			   al.colourbackground                AS ColourBackground,
			   al.colourfont                      AS ColourFont,
			   al.personbackgroundcolour          AS PersonBackgroundColour,
			   al.personfontcolour                AS PersonFontColour,
			   al.WeekDuration                    AS WeekDuration,
			   AL.AccPeriod                       AS AccPeriod,
			   al.CostCode                        AS CostCode,
			   al.staffid                         AS StaffID,
			   al.TriangleColour                  AS TriangleColour,
			   AL.Login                           AS Login,
			   AL.CountLeave                      AS CountLeave,
			   AL.LeaveApproved                   AS LeaveApproved,
			   AL.LeaveDeleted                    AS LeaveDeleted,
			   AL.LeaveShortNotice                AS LeaveShortNotice,
			   AL.Leaveoversummer                 AS Leaveoversummer,
			   AL.LeaveisOK                       AS LeaveisOK,
			   AL.IDLeave	                      AS IDLeave,
			   AL.EditDuty                        AS EditDuty,
			   AL.AllowOverLimit                  AS AllowOverLimit,
			   AL.day_0,
			   AL.day_1,
			   AL.day_2,
			   AL.day_3,
			   AL.day_4,
			   AL.day_5,
			   AL.day_6,
			   AL.AffectLocks,
			   AL.RequestsAllowed,			   
			   AL.RequestISOK                     AS RequestISOK, 
			   AL.RequestApproved                 AS RequestApproved,  
			   AL.LockRow                         AS LockRow,
			   AL.ShowEDPIcon                     AS ShowEDPIcon,
			   CASE WHEN WTD.isapproved = 0 THEN 'cross-red'
				    WHEN WTD.isapproved = 1 THEN 'cross-blue'
				 ELSE ''
			    END                    AS WTDBreachClassName
		       INTO #TempGetAllocations
	   FROM
		   ( SELECT AL.AllocateInstanceID,
					AL.DepartmentID,
					AL.AllocationID,
					AL.DutyName,
					AL.Duration,
					AL.WeekNumber,
					AL.iDay,
					AL.StartTime,
					AL.EndTime,
					AL.ActingGrade,
					AL.LeaveID,
					AL.ManualERR,
					AL.DutyComments,
					AL.BaseCode,
					AL.BackColour,
					AL.FontColour,
					AL.PersonComments,
					AL.AdhocDuty,
					AL.MarkedOvertime,
					AL.MarkedPTExtraDay,
					AL.MarkedCompLeave,
					AL.MarkedSickness,
					AL.ManualOTAmount,
					AL.ManualOTExcBreaksAmount,
					AL.UnAllocated,
					AL.ID,
					AL.SchedulingTeamId,
					AL.SchedulingPersonID,
					AL.DutyDate,
					AL.StartDate,
					AL.EndDate,
					AL.isAttention,
					AL.isRequest,
					AL.aftermidnight,
					AL.dutyProgramId,
					AL.dutyBreakTime,
					AL.dutyColorId,
					AL.isPublished,
					AL.IsHomeTeam,
					AL.MarkWiad,
					AL.MarkActual,
					AL.isEdited,
					AL.MannualOThours,
					AL.IsActive,
					AL.MarkWTD,
					AL.WTDComments,
					AL.isEditable,
					AL.OrigAllocationID,
					AL.MasterDutyId,
					AL.isActiveDuty,
					AL.isCompareEdited,
				    rft.paymenttypename,
				    sd.forename,
				    sd.surname,
				    sd.preferredforename,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( sd.preferredforename IS NULL
								   OR sd.preferredforename = '''' ) THEN (
						   sd.forename + '''' + sd.surname )
						   ELSE ( sd.preferredforename + '''' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,
				   spl.sortcode                                AS sortcode,
				   sct.eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   sco.EDPMinimumExcBreaks                     AS ContractedHours,
				   sco.accdays                                 AS accdays,
				   sco.manualedp                               AS manualedp,
				   signin.active                               AS signin,
				   signin.inbuilding                           AS inbuilding,
				   case when signin.active = 1 and signin.inbuilding = 1 then 'blue_tick.png'
				        when signin.active = 1 and signin.inbuilding <> 1 then 'green_tick.png'
						when signin.active = 2  then 'red_cross.png'
						else  'red_cross.png' end              AS ImageNameSignin,
				   case when signin.active = 1 and signin.inbuilding = 1 then 0
				        when signin.active = 1 and signin.inbuilding <> 1 then 0
						when signin.active = 2  then 1
						else  1 end                            AS ActionNameForSignin,
				   signin.starttime                            AS SignInStartTime,
				   signin.endtime                              AS SignInEndTime,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   spl.backgroundcolour                        AS PersonBackgroundColour,
				   spl.fontcolour                              AS PersonFontColour,
				   sd.staffnumber                              AS staffnumber,
				   sct.costcode                                AS CostCode,
				   sd.staffid                                  AS staffid,
				   cg.TriangleColour                           AS TriangleColour,
				   LA.Login                                    AS Login,
				   LA.CountLeave                               AS CountLeave,
				   LA.Approved                                 AS LeaveApproved,
				   LA.Deleted                                  AS LeaveDeleted,
				   LA.ShortNotice                              AS LeaveShortNotice,
				   LA.oversummer                               AS Leaveoversummer,
				   LA.isOK                                     AS LeaveisOK,
				   LA.ID	                                   AS IDLeave, 
				   RT.AllowOverLimit                           AS AllowOverLimit,
				   RT.day_0,
				   RT.day_1,
				   RT.day_2,
				   RT.day_3,
				   RT.day_4,
				   RT.day_5,
				   RT.day_6,
				   RT.AffectLocks,
				   RT.RequestsAllowed,
				   RQ.isOK                                     AS RequestISOK, 
				   RQ.Approved                                 AS RequestApproved,  
				   LR.ID                                       AS LockRow,
				   CASE WHEN EDP.ID IS NOT NULL 
				        THEN 1 ELSE 0 END                      AS ShowEDPIcon,
				   CASE WHEN TA.totalduration is null 
				    THEN '00.00' ELSE CAST(TA.totalduration AS VARCHAR) END      AS WeekDuration,
				    CAST(TA.currentweek AS VARCHAR)+'/'+CAST(TA.totalweeks AS VARCHAR) AS AccPeriod,
					CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 
					      AND AL.DutyTeamID <> AL.schedulingTeamId THEN 0 
						 WHEN ISNULL(AL.ishometeam,1) = 0 and ISNULL(AL.MarkWIAD,0) = 0 
						  AND ISNULL(AL.MarkActual,0) = 0 THEN 0
						 WHEN ISNULL(AL.ishometeam,1) = 1 
						  AND ( ISNULL(AL.MarkWIAD,0) = 1 OR ISNULL(AL.MarkActual,0) = 1 ) THEN 0
						 WHEN UPPER(AL.DutyName) like '%SICK%' THEN 0
						ELSE 1 END          AS EditDuty							
			  FROM #TempAllocations AS AL
			 INNER JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON sp.scheduledpersonid = spl.scheduledpersonid
							  AND spl.teamid=AL.SchedulingTeamId
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
			 LEFT JOIN StaffContract sct (nolock) ON sd.staffid = sct.staffid
							 AND AL.dutydate BETWEEN ISNULL(sct.startdate,AL.dutydate)
							                AND ISNULL( sct.enddate,AL.dutydate)
											AND ISNULL( SCT.isactive,1) = 1
			 LEFT JOIN StaffConfig sco (nolock) ON sd.staffid = sco.staffid
							 AND AL.dutydate BETWEEN ISNULL(sco.startdate,AL.dutydate) 
							                AND ISNULL( sco.enddate,AL.dutydate)
											AND ISNULL( SCO.isactive,1) = 1
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = sco.accgroupid
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = sco.paymenttypeid
			LEFT JOIN signin ON signin.schedulingpersonid = AL.schedulingpersonid
							 AND ( signin.iweek = AL.weeknumber AND signin.iday = AL.iday )
			LEFT JOIN REF_MasterDutyColours mdc ON mdc.masterdutycolourid = AL.dutycolorid
			LEFT JOIN LeaveApplications LA  (nolock) ON LA.login = sd.netlogin and AL.DutyDate = LA.dDate
			LEFT JOIN #TempCharging CG ON AL.ID = CG.AllocationId AND AL.MasterDutyId = CG.MasterDutyId	
            LEFT JOIN #TempAccounting TA on TA.schedulingpersonid = AL.schedulingpersonid and TA.WeekNumber = AL.Weeknumber		
			LEFT JOIN Requests RQ ON RQ.dDate = AL.DutyDate AND RQ.ScheduledPersonID = AL.SchedulingPersonID
			                                                AND RQ.Deleted = 0
			LEFT JOIN RequestTypes (Nolock) RT ON RQ.RequestType = RT.ID
			LEFT JOIN LockRequests LR ON LR.WeekNumber = AL.WeekNumber AND LR.iDay = AL.iDay 
			                          AND LR.deleted = 0
			      AND LR.ScheduledPersonID = AL.SchedulingPersonID
		    LEFT JOIN EDP ON EDP.SchedulingTeamId = AL.SchedulingTeamId
			      AND EDP.SchedulingPersonID = AL.SchedulingPersonID
				  AND EDP.ddate = AL.dutydate
		     WHERE CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
			   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))			   
		) AL LEFT JOIN
		(
			select schedulingpersonid, 
			       weeknumber, 
				   iday,
				   min(isapproved) isapproved
			FROM (
			SELECT wtd.schedulingTeamId, 
			       wtd.schedulingpersonid, 
				   wtd.breachtype, 
				   td.ixYearWeek weeknumber, 
				   td.ixDayInWeek iday, 
				   wtd.isapproved
			  FROM working_time_directive WTD
			 INNER JOIN TimeDimension TD ON td.dDateTime between wtd.StartDate and wtd.enddate
			 WHERE WTD.schedulingTeamID = @pteamid
			   AND StartDate <= @EndDate 
			   AND EndDate >= @StartDate
			  ) FD GROUP BY schedulingpersonid, weeknumber, iday
		) WTD ON WTD.schedulingpersonid = AL.schedulingpersonid 
		     and WTD.weeknumber = AL.WeekNumber
			 AND WTD.iday = AL.iday
		
		DROP table IF EXISTS #TempAllocations
		DROP TABLE IF EXISTS #AllocationBreachData
		DROP TABLE IF EXISTS #TempAllocationsWTD
		DROP TABLE IF EXISTS #TempCharging 
		DROP TABLE IF EXISTS #TempAccounting
		
		IF ( ISNULL(@filterOrderCond,'') <> '')
		 BEGIN
			SET @filterOrderCond = replace( @filterOrderCond, 'sp.DisplayName', 'DisplayName')
			SET @filterOrderCond = replace( @filterOrderCond, 'spl.SortCode', 'sortcode')
			SET @filterOrderCond = replace( @filterOrderCond, 'a.DutyName', 'DutyName')
			SET @filterOrderCond = replace( @filterOrderCond, 'sct.CostCode','CostCode') 		
			SET @filterOrderCond = replace( @filterOrderCond, 'aj.JobName', 'JobName')
			SET @filterOrderCond = replace( @filterOrderCond, 'aj.ProgrammeId','ProgrammeId') 	
			SET @filterOrderCond = replace( @filterOrderCond, 'spsl.programmes_id','programmes_id') 	
			SET @filterOrderCond = replace( @filterOrderCond, 'a.StartTime','StartTime')	
         END		
				
		IF ( ISNULL(@filterCond,'') = '')
		  BEGIN
		   
		   SET @FilterSQL = 'SELECT * FROM #TempGetAllocations '+ ISNULL(@filterOrderCond,' ')

		  END
		ELSE
		 BEGIN
		   		  
			  SET @VFilter = replace( @filterCond, 'sp.DisplayName', 'DisplayName')
			  SET @VFilter = replace( @VFilter, 'spl.SortCode', 'sortcode')
			  SET @VFilter = replace( @VFilter, 'a.DutyName', 'DutyName')
			  SET @VFilter = replace( @VFilter, 'sct.CostCode','CostCode') 
			  SET @VFilter = replace( @VFilter, 'a.StartTime','StartTime')
			  		  
			  IF ( CHARINDEX ('aj.JobName',@VFilter) > 0 OR CHARINDEX ('aj.ProgrammeId',@VFilter) > 0 )
			   BEGIN
			     
				 SET @vleftjoinflag = 1
				 
                  select AJ.AllocationID, AJ.JobName, AJ.ProgrammeID
				         INTO #TempJobs
				  from Allocations_Jobs AJ
				  INNER JOIN #TempGetAllocations AL ON AL.ID= AJ.AllocationID
				  
                  SET @VLJFilter = 'LEFT JOIN #TempJobs ON #TempGetAllocations.ID=#TempJobs.AllocationID '
                                     									 
			  
			   END	
			   
			  SET @VFilter = replace( @VFilter, 'aj.JobName', 'JobName')
			  SET @VFilter = replace( @VFilter, 'aj.ProgrammeId','ProgrammeId') 	

			  IF ( CHARINDEX ('spsl.programmes_id',@VFilter) > 0 )
			   BEGIN
			  
			    SET @vleftjoinflag = 1
			  
				  SELECT AL.ID, programmes_id
				         INTO #TempStaffSkills
				    FROM skills_programmes_staff_link spsl 
					INNER JOIN #TempGetAllocations AL ON spsl.staff_id = AL.StaffID 
					
                  SET @VLJFilter = @VLJFilter + ' LEFT JOIN #TempStaffSkills  ON #TempGetAllocations.ID=#TempStaffSkills.ID '
									 
			   END				  
		  
			  SET @VFilter = replace( @VFilter, 'spsl.programmes_id','programmes_id') 	
			  
			  IF (@vleftjoinflag = 1)
			    BEGIN
				
				  SET @FilterSQL = ' SELECT * FROM #TempGetAllocations WHERE ID IN ( 
				                 SELECT ID FROM #TempGetAllocations '
								 +@VLJFilter+' '+@VFilter+' ) '+@filterOrderCond		 
				
				END
			  ELSE
			    BEGIN
				  
				  SET @FilterSQL = ' SELECT * FROM #TempGetAllocations WHERE 1=1 '+ @VFilter+' '+@filterOrderCond
				
				END
			  
		  END	  

         EXEC ( @FilterSQL )
		
END