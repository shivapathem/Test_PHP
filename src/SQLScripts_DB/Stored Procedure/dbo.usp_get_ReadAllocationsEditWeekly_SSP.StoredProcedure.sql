USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsEditWeekly_SSP]    Script Date: 10/07/2025 14:04:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                  PROCEDURE  [dbo].[usp_get_ReadAllocationsEditWeekly_SSP]
@startDate                 VARCHAR(22),
@EndDate                   VARCHAR(22),
@pteamId			       INT,
@filterCond                VARCHAR(MAX) = NULL,
@filterOrderCond           VARCHAR(MAX) = NULL,
@SchedulingPersonID        INT = NULL,
@ShowUnAllocatedDuty       INT = NULL,
@pNetLogin                 VARCHAR(30) = NULL,
@DutyFilterID              INT = NULL,
@SkillFilters              VARCHAR(MAX) = NULL,
@DutyLabelFilters          VARCHAR(MAX) = NULL


AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

    DECLARE @FilterSQL                 VARCHAR(MAX)
	DECLARE @VFilter                   VARCHAR(MAX)
	DECLARE @VLJFilter                 VARCHAR(MAX)
	DECLARE @FilterSetFlag             INT = 0
	DECLARE @vleftjoinflag             INT = 0
	DECLARE @ShowOnlyUnAllocatedDuty   INT = 0
	DECLARE @ReturnValue               INT = 1
	DECLARE @NoOfWeeks                 INT
	DECLARE @NoOfSkills                INT = 0
	DECLARE @NoOfLabels                INT = 0
	DECLARE @IsAndFilter               INT = 0
	DECLARE @ArchiveDataFlag           BIT = 0,
	        @AllocDataFlag             BIT = 0,
	        @TempAllocExistsFlag       BIT = 0;

	DECLARE @TempLabelList VARCHAR(MAX);

    DECLARE @TempAccounting TABLE (staffid INT, 
	                               schedulingpersonid INT,
								   SchedulingTeamId INT,
								   startdate DATETIME,
								   enddate DATETIME,
								   startweek INT,
								   endweek INT,
								   WeekNumber INT,
								   totalweeks INT,
								   currentweek INT,
								   totalduration INT,
								   totalplannedduration INT,
								   NoOfDays INT,
								   OverTimeHrs INT,
								   IsHomeTeam BIT)	
								   
    DECLARE @TempCharging	TABLE (AllocationId INT,
                                   MasterDutyId INT,
								   TriangleColour VARCHAR(15) )
								   
    DECLARE @TempRequest	TABLE (WeekNumber INT,
								   iday INT,
								   schedulingpersonid INT,
								   ShowLock INT,
								   LockIconColour VARCHAR(10),
								   ReqCount INT)
	
	
	IF ( ISNULL(@SchedulingPersonID,0) = 0 AND ISNULL(@ShowUnAllocatedDuty,0) = 1 )
	 BEGIN
	  SET @ShowOnlyUnAllocatedDuty = 1
	 END
	 
	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1	 	
	
	IF EXISTS ( select top 1 TD.ID 
				  from ArchivedWeeks AW
				 inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
				 where td.dDateTime between @startDate and @EndDate
			   )
		SET @ArchiveDataFlag = 1

	IF EXISTS (  select top 1 TD1.ID 
				   from TimeDimension TD1
				  WHERE NOT EXISTS 
								 (
								  select TD.ID 
								   from ArchivedWeeks AW
								   inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
								   where TD1.ID = TD.ID
								 )
		            and TD1.dDateTime between @startDate and @EndDate
				)
		 SET @AllocDataFlag = 1
	 
	
	SELECT @NoOfWeeks = COUNT(DISTINCT td.ixYearWeek)  
	 FROM TimeDimension td 
	WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101)  
	
	  IF ( @ShowOnlyUnAllocatedDuty <> 1 AND @NoOfWeeks <=2 )
	   BEGIN
	   
		INSERT INTO @TempAccounting
		SELECT distinct sd.staffid, 
		       sp.ScheduledPersonID,
			   SPL.TeamID,
			   AGD.AccPeriodStartDate as startdate , 
			   AGD.AccPeriodEndDate as enddate, 
			   AGD.AccPeriodStartWeek as startweek, 
			   AGD.AccPeriodEndWeek as endweek,
			   TD.ixYearWeek weeknumber, 
			   AccPeriodWeeks as totalweeks,
			   WeekOfAccPeriod as currentweek,
			   0 as totalduration,
			   0 as totalplannedduration,
			   0 as NoOfDays,
			   0 AS OverTimeHrs,
			   spl.IsHomeTeam
		  FROM ScheduledPeople AS sp (nolock) 
		 INNER JOIN ScheduledPersonTeam_LINK  AS spl (nolock) ON sp.scheduledpersonid = spl.scheduledpersonid
		 INNER JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
		 INNER JOIN Staffconfig_Processed SCP ON Sd.Staffid=SCP.StaffId		 
		 INNER join Timedimension TD on 1=1
		 INNER JOIN REF_AccountingGroup_Dates AGD ON AGD.BBCWeek=TD.ixYearWeek AND SCP.AccGroupID=AGD.AccGroupID
		 WHERE (TD.dDateTime between  SCP.StartDate and SCP.EndDate ) 
		   and TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
		   AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
		   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
		   AND CONVERT(DATETIME,@startDate,101) <=  isnull( AGD.AccPeriodEndDate, CONVERT(DATETIME,@startDate,101) ) 
		   AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( AGD.AccPeriodStartDate, CONVERT(DATETIME,@EndDate,101))
		   AND spl.scheduledType = 1
		   AND SPL.TeamID = @pteamId	
		   AND SP.scheduledpersonid = case when isnull(@SchedulingPersonID,0) > 0 
											 then @SchedulingPersonID 
											 else SP.scheduledpersonid 
											 end	

			 update TC
				set TC.SchedulingTeamId = spl.TeamID
			   from @TempAccounting TC
			  inner join ScheduledPersonTeam_LINK SPL on spl.ScheduledPersonID = tc.schedulingpersonid
			  inner join schedulingTeams st on st.schedulingTeamId = spl.TeamID
		      WHERE CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
		       AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))	
			   AND spl.IsHomeTeam = 1
			   AND TC.IsHomeTeam = 0
			   AND spl.scheduledType = 1
			   AND ST.schedulingTeamName not in ('Other BBC', 'Freelancers','Apprentices')	

             update TC
				set TC.totalplannedduration = AR.AccPeriodDuration
			   from @TempAccounting TC
			  inner join AccPeriodROTADurationSummary AR on tc.schedulingpersonid = AR.ScheduledPersonID
			         and tc.startdate = AR.AccPeriodStartDate
					 and tc.enddate = AR.AccPeriodEndDate


	IF EXISTS ( select top 1 TD.ID 
				  from ArchivedWeeks AW
				 inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
				 where exists ( select 1
				                  from @TempAccounting TA
								 where td.dDateTime between ta.startdate and ta.enddate 
							   )
			   )
        BEGIN
			 update TC
				set TC.totalduration = AC.totaldtn,
				    TC.NoOfDays = AC.NoOfDays,
					TC.OverTimeHrs = AC.OverTimeHrs
			   from @TempAccounting TC
			  inner join 
			  (
			 select TA.schedulingpersonid,
					ta.weeknumber, 
					sum( case when isnull(markwiad,0)=1 then 0 
					      else isnull(al.duration,0)-isnull(al.dutyBreakTime,0) end) as totaldtn,
					sum( case when isnull(markwiad,0)=1 then 0 
					          when isnull(markwiad,0)=0 AND isnull(al.duration,0) > 0 THEN 1 end) as NoOfDays,
					sum( isnull(MannualOThours,0)) as OverTimeHrs
			   from @TempAccounting TA
			  inner join Allocations_Archive AL (nolock) on TA.schedulingpersonid = AL.schedulingpersonid
				AND TA.SchedulingTeamId = AL.SchedulingTeamId 
			  INNER JOIN TimeDimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iday
			  WHERE td.dDateTime between ta.startdate and ta.enddate				  
			  group by TA.schedulingpersonid,ta.weeknumber
			  ) AC ON TC.schedulingpersonid = AC.schedulingpersonid 
				  AND TC.WeekNumber = AC.Weeknumber	
		 END


	IF EXISTS (  select top 1 TD1.ID 
				   from TimeDimension TD1
				  WHERE NOT EXISTS 
								 (
								  select TD.ID 
								   from ArchivedWeeks AW
								   inner join TimeDimension td on td.ixYearWeek = aw.WeekNumber
								   where TD1.ID = TD.ID
								 )
		            and exists ( select 1
				                  from @TempAccounting TA
								 where TD1.dDateTime between ta.startdate and ta.enddate 
							   )
				)
		  BEGIN
			 update TC
				set TC.totalduration = AC.totaldtn,
				    TC.NoOfDays = AC.NoOfDays,
					TC.OverTimeHrs = AC.OverTimeHrs
			   from @TempAccounting TC
			  inner join 
			  (
			 select TA.schedulingpersonid,
					ta.weeknumber, 
					sum( case when isnull(markwiad,0)=1 then 0 
					      else isnull(al.duration,0)-isnull(al.dutyBreakTime,0) end) as totaldtn,
					sum( case when isnull(markwiad,0)=1 then 0 
					          when isnull(markwiad,0)=0 AND isnull(al.duration,0) > 0 THEN 1 end) as NoOfDays,
					sum( isnull(MannualOThours,0)) as OverTimeHrs
			   from @TempAccounting TA
			  inner join Allocations AL (nolock) on TA.schedulingpersonid = AL.schedulingpersonid
				AND TA.SchedulingTeamId = AL.SchedulingTeamId 
			  INNER JOIN TimeDimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iday
			  WHERE td.dDateTime between ta.startdate and ta.enddate				  
			  group by TA.schedulingpersonid,ta.weeknumber
			  ) AC ON TC.schedulingpersonid = AC.schedulingpersonid 
				  AND TC.WeekNumber = AC.Weeknumber	
		  END


	   END
				  
	  IF @ShowOnlyUnAllocatedDuty <> 1 
	   BEGIN			  

	   IF ( @AllocDataFlag = 1 )
	    BEGIN
		 INSERT	INTO @TempCharging				  
		 SELECT CL.AllocationId, 
		        CL.MasterDutyId,
				case
				when sum(case when CL.IsActual = 2 then 2 else 1 end) = (2 * count(CL.ChargingId)) and count(CL.ChargingId) > 0 then 'Yellow'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Green'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 'Red'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Blue'
				else 'None' end as TriangleColour 
		   from ChargingDutyMapping_Link CL (nolock)
		  INNER JOIN Allocations AL (nolock) ON AL.ID = CL.AllocationId AND AL.MasterDutyId = CL.MasterDutyId
		  INNER join Timedimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iday
		  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			AND AL.SchedulingTeamId = @pteamId
			AND AL.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
											 then @SchedulingPersonID 
											 else al.SchedulingPersonID 
											 end		
		  GROUP by CL.AllocationId, CL.MasterDutyId	

         INSERT INTO @TempRequest(WeekNumber,
		              iday,
					  schedulingpersonid,
					  ShowLock,
					  LockIconColour,
					  ReqCount)         
		 SELECT DISTINCT WeekNumber,
		        iday,
				schedulingpersonid,
				max(ShowLock) AS ShowLock,
				max(LockIconColour) AS LockIconColour,
				ReqCount
		 FROM 
		 (
		 SELECT Al.WeekNumber, 
		        AL.iday, 
		        Al.schedulingpersonid,
				CASE when LR.ID > 0 THEN 1
				     WHEN RQ.ID > 0 THEN 1
				ELSE 0 END AS IsRequestAvailable,
		        CASE WHEN LR.ID > 0 THEN 1 
		             WHEN RT.AffectLocks = 1 AND RQ.Approved = 1 THEN 1
					 ELSE 0 END AS ShowLock,
		        CASE WHEN RQ.Approved = 1 AND RT.RequestsAllowed >= 1 THEN 'O'
				     WHEN ( CASE WHEN AL.iday=0 THEN RT.day_0
					             WHEN AL.iday=1 THEN RT.day_1
								 WHEN AL.iday=2 THEN RT.day_2
								 WHEN AL.iday=3 THEN RT.day_3
								 WHEN AL.iday=4 THEN RT.day_4
								 WHEN AL.iday=5 THEN RT.day_5
								 WHEN AL.iday=6 THEN RT.day_6
							 END) = -1 THEN 'B'
					 WHEN RQ.isOK = 1 AND RT.RequestsAllowed >= 1 THEN 'Y'
					 WHEN RT.AllowOverLimit = 1 THEN 'G' ELSE '' END AS LockIconColour,
                COUNT (rq.scheduledpersonid) OVER ( Partition by rq.ddate, rq.scheduledpersonid) as ReqCount
           FROM Allocations AL (nolock)
		  INNER join Timedimension TD (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek			   
		   LEFT JOIN Requests RQ (nolock) ON RQ.dDate = AL.DutyDate AND RQ.ScheduledPersonID = AL.SchedulingPersonID 
			                                                AND RQ.Deleted = 0
		   LEFT JOIN RequestTypes (Nolock) RT ON RQ.RequestType = RT.ID
		   LEFT JOIN LockRequests LR (nolock) ON LR.WeekNumber = AL.WeekNumber AND LR.iDay = AL.iDay 
			                          AND LR.ScheduledPersonID = AL.SchedulingPersonID  
			                          AND LR.deleted = 0
		  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			AND AL.SchedulingTeamId = @pteamId	
			AND AL.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
											 then @SchedulingPersonID 
											 else al.SchedulingPersonID 
											 end
            ) FD where IsRequestAvailable = 1	
			GROUP BY WeekNumber,
					 iday,
					 schedulingpersonid,
					 ReqCount		
		END

	   IF ( @ArchiveDataFlag = 1 )
	    BEGIN
		 INSERT	INTO @TempCharging				  
		 SELECT CL.AllocationId, 
		        CL.MasterDutyId,
				case
				when sum(case when CL.IsActual = 2 then 2 else 1 end) = (2 * count(CL.ChargingId)) and count(CL.ChargingId) > 0 then 'Yellow'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Green'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 'Red'
				when sum(case when CL.IsActual = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 'Blue'
				else 'None' end as TriangleColour 
		   from ChargingDutyMapping_Link CL (nolock)
		  INNER JOIN Allocations_Archive AL (nolock) ON AL.ID = CL.AllocationId AND AL.MasterDutyId = CL.MasterDutyId
		  INNER join Timedimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iday
		  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			AND AL.SchedulingTeamId = @pteamId
			AND AL.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
											 then @SchedulingPersonID 
											 else al.SchedulingPersonID 
											 end		
		  GROUP by CL.AllocationId, CL.MasterDutyId	

         INSERT INTO @TempRequest(WeekNumber,
		              iday,
					  schedulingpersonid,
					  ShowLock,
					  LockIconColour,
					  ReqCount)         
		 SELECT DISTINCT WeekNumber,
		        iday,
				schedulingpersonid,
				max(ShowLock) AS ShowLock,
				max(LockIconColour) AS LockIconColour,
				ReqCount
		 FROM 
		 (
		 SELECT Al.WeekNumber, 
		        AL.iday, 
		        Al.schedulingpersonid,
				CASE when LR.ID > 0 THEN 1
				     WHEN RQ.ID > 0 THEN 1
				ELSE 0 END AS IsRequestAvailable,
		        CASE WHEN LR.ID > 0 THEN 1 
		             WHEN RT.AffectLocks = 1 AND RQ.Approved = 1 THEN 1
					 ELSE 0 END AS ShowLock,
		        CASE WHEN RQ.Approved = 1 AND RT.RequestsAllowed >= 1 THEN 'O'
				     WHEN ( CASE WHEN AL.iday=0 THEN RT.day_0
					             WHEN AL.iday=1 THEN RT.day_1
								 WHEN AL.iday=2 THEN RT.day_2
								 WHEN AL.iday=3 THEN RT.day_3
								 WHEN AL.iday=4 THEN RT.day_4
								 WHEN AL.iday=5 THEN RT.day_5
								 WHEN AL.iday=6 THEN RT.day_6
							 END) = -1 THEN 'B'
					 WHEN RQ.isOK = 1 AND RT.RequestsAllowed >= 1 THEN 'Y'
					 WHEN RT.AllowOverLimit = 1 THEN 'G' ELSE '' END AS LockIconColour,
                COUNT (rq.scheduledpersonid) OVER ( Partition by rq.ddate, rq.scheduledpersonid) as ReqCount
           FROM Allocations_Archive AL (nolock)
		  INNER join Timedimension TD (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek			   
		   LEFT JOIN Requests RQ (nolock) ON RQ.dDate = AL.DutyDate AND RQ.ScheduledPersonID = AL.SchedulingPersonID 
			                                                AND RQ.Deleted = 0
		   LEFT JOIN RequestTypes (Nolock) RT ON RQ.RequestType = RT.ID
		   LEFT JOIN LockRequests LR (nolock) ON LR.WeekNumber = AL.WeekNumber AND LR.iDay = AL.iDay 
			                          AND LR.ScheduledPersonID = AL.SchedulingPersonID  
			                          AND LR.deleted = 0
		  WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			AND AL.SchedulingTeamId = @pteamId	
			AND AL.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
											 then @SchedulingPersonID 
											 else al.SchedulingPersonID 
											 end
            ) FD where IsRequestAvailable = 1	
			GROUP BY WeekNumber,
					 iday,
					 schedulingpersonid,
					 ReqCount		
		END
	    
       END		
	   

	IF @ShowOnlyUnAllocatedDuty = 1 
	 BEGIN
	  
	  IF (  @AllocDataFlag = 1 )
	   BEGIN
	    SET @FilterSQL = ' 
	    SELECT DutyName,
			   Duration,
			   WeekNumber,
			   iDay,
			   StartTime,
			   EndTime,
			   AL.ID,
			   ST.SchedulingTeamId,
			   SchedulingPersonID,
			   FORMAT(AL.dutydate, ''yyyy-MM-dd'') AS DutyDate,
			   MasterDutyId,
			   AL.isActive,
			   DutyTeamID,
			   ''UL'' AS DisplayGrid ,
			   count(DutyName) over ( partition by dutydate, dutyname, schedulingpersonid ) AS DutyInstances
          FROM Allocations AS AL (nolock)
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iday		
		     INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  AL.SchedulingTeamId 													 
		     WHERE AL.DutyName is not null
			   AND ISNULL(AL.SchedulingPersonID,0) = 0 
			   AND ISNULL(AL.isActive,1) = 1 
			   AND ST.SchedulingTeamId = '+CAST(@pteamId	AS VARCHAR)+'
			   AND TD.dDateTime between CONVERT(DATETIME,'''+@startDate+''',101) and CONVERT(DATETIME,'''+@EndDate+''',101) '
		END
	  IF (  @ArchiveDataFlag = 1 )
	   BEGIN
	    SET @FilterSQL = ' 
	    SELECT DutyName,
			   Duration,
			   WeekNumber,
			   iDay,
			   StartTime,
			   EndTime,
			   AL.ID,
			   ST.SchedulingTeamId,
			   SchedulingPersonID,
			   FORMAT(AL.dutydate, ''yyyy-MM-dd'') AS DutyDate,
			   MasterDutyId,
			   AL.isActive,
			   DutyTeamID,
			   ''UL'' AS DisplayGrid ,
			   count(DutyName) over ( partition by dutydate, dutyname, schedulingpersonid ) AS DutyInstances
          FROM Allocations_Archive AS AL (nolock)
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iday		
		     INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  AL.SchedulingTeamId 													 
		     WHERE AL.DutyName is not null
			   AND ISNULL(AL.SchedulingPersonID,0) = 0 
			   AND ISNULL(AL.isActive,1) = 1 
			   AND ST.SchedulingTeamId = '+CAST(@pteamId	AS VARCHAR)+'
			   AND TD.dDateTime between CONVERT(DATETIME,'''+@startDate+''',101) and CONVERT(DATETIME,'''+@EndDate+''',101) '
		END

             IF ( ISNULL(@DutyFilterID,0) > 0 )
		       BEGIN

			    SET @FilterSQL = @FilterSQL +' AND DutyName IN (SELECT DISTINCT md.DutyName 
					                                       FROM MasterDutiesFilterLinks mdfl
														   JOIN MasterDuties md ON md.MasterDutyID = mdfl.MasterDutyID
														  WHERE mdfl.FilterID = '+ cast(@DutyFilterID AS VARCHAR)+ ') '
			   END
                			   

			   
     END
    ELSE
     BEGIN

					SELECT @TempLabelList =   '#'+STUFF(
					(SELECT '#' + cast(id  as varchar)+'#'
					     from Programmes
					    where Programme = 'Acting'
					      FOR XML PATH(''), TYPE
						).value('.', 'VARCHAR(MAX)'),1,1,'')

	    SELECT al.staffnumber                     AS StaffNumber,
			   al.dutyname                        AS DutyName,
			   al.duration                        AS Duration,
			   al.weeknumber                      AS WeekNumber,
			   al.iday                            AS iDay,
			   al.starttime                       AS StartTime,
			   al.endtime                         AS EndTime,
			   al.sortcode                        AS SortCode,
			   al.dutycomments                    AS DutyComments,
			   al.personcomments                  AS PersonComments,
			   al.markedovertime                  AS MarkedOvertime,
			   al.markedsickness                  AS MarkedSickness,
			   al.unallocated                     AS UnAllocated,
			   al.id                              AS ID,
			   al.schedulingteamid                AS SchedulingTeamId,
			   al.schedulingpersonid              AS SchedulingPersonID,
			   FORMAT(al.dutydate, 'yyyy-MM-dd')  AS DutyDate,
			   al.ispublished                     AS isPublished,
			   al.ishometeam                      AS IsHomeTeam,
			   al.markwiad                        AS MarkWiad,
			   al.markactual                      AS MarkActual,
			   al.isattention                     AS isAttentionClsName,
			   al.isrequest                       AS isRequest,
			   al.dutybreaktime                   AS dutyBreakTime,
			   al.dutycolorid                     AS dutyColorId,
			   al.mASterdutyid                    AS MasterDutyId,
			   al.isactive                        AS isActive,
			   al.iseditable                      AS isEditable,
			   al.paymenttypename                 AS pay,
			   al.displayname                     AS DisplayName,
			   al.DisplayLastName                 AS DisplayLastName,
			   al.DisplayFirstName                AS DisplayFirstName,
			   al.schedulingTeamName              AS schedulingTeamName,
			   al.IsSigninAllowed                 AS IsSigninAllowed,
			   al.Signindays                      AS Signindays,			   
			   al.eft                             AS EFT,
			   al.acc                             AS ACC,
			   al.contractedhours                 AS ContractedHours,
			   CASE WHEN al.accdays IS NULL THEN  AL.CurrWeekNoOfDays
			        ELSE al.accdays END           AS AccDays,
			   al.OverTimeHrs                     AS OverTimeHrs,
			   al.manualedp                       AS ManualEDP,
			   al.signin                          AS signin,
			   al.inbuilding                      AS inbuilding,
               al.ActionNameForSignin             AS ActionNameForSignin,
			   al.ImageNameSignin                 AS ImageNameSignin,			   
			   al.colourbackground                AS ColourBackground,
			   al.colourfont                      AS ColourFont,
			   al.personbackgroundcolour          AS PersonBackgroundColour,
			   al.personfontcolour                AS PersonFontColour,
			   CASE WHEN AL.WeekDuration is null then 
			             CASE WHEN AL.CurrWeekDuration = 0 THEN '00.00'
						      ELSE CAST(AL.CurrWeekDuration AS VARCHAR)
                         END							  
					ELSE CAST(AL.WeekDuration AS VARCHAR) END   AS WeekDuration,
			   AL.AccPeriod                       AS AccPeriod,
			   al.CostCode                        AS CostCode,
			   al.staffid                         AS StaffID,
			   al.TriangleColour                  AS TriangleColour,
			   AL.CountLeave                      AS CountLeave,
			   AL.LeaveApproved                   AS LeaveApproved,
			   AL.LeaveDeleted                    AS LeaveDeleted,
			   AL.LeaveShortNotice                AS LeaveShortNotice,
			   AL.Leaveoversummer                 AS Leaveoversummer,
			   AL.LeaveisOK                       AS LeaveisOK,
			   AL.EditDuty                        AS EditDuty,
			   AL.ShowLock,
			   AL.LockIconColour,
			   AL.ReqCount,
			   AL.ShowEDPIcon                     AS ShowEDPIcon,
			   AL.ShowWIAD                        AS ShowWIAD,
			   CASE WHEN WTD.isapproved = 0 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 1
				    WHEN WTD.isapproved = 1 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 2
					WHEN WTD.isapproved = 0 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 1
				    WHEN WTD.isapproved = 1 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 2
				 ELSE 0
			    END                               AS WTDBreachClassName,
			   AL.contextMenuClsName              AS contextMenuClsName,
			   AL.DisplayGrid                     AS DisplayGrid,
			   AL.MarkOverTwelve                  AS MarkOverTwelve,
			   AL.IsUnderElevenBreak              AS IsUnderElevenBreak,
			   AL.IsUnderElevenBreakOverride      AS IsUnderElevenBreakOverride,
			   AL.DutyTeamID 			          AS DutyTeamID,
			   AL.IsDutyFromOtherTeam             AS IsDutyFromOtherTeam,
			   AL.dutyProgramId                   AS dutyProgramId,
			   count(AL.DutyName) over ( partition by AL.dutydate, AL.dutyname,AL.schedulingpersonid) AS DutyInstances,
			   AL.LeaveStartTime                  AS LeaveStartTime,
			   AL.LeaveEndTime                    AS LeaveEndTime,
			   AL.LeavePDL                        AS LeavePDL,
			   AL.FWANotesFlag                    AS FWANotesFlag,
			   AL.NetLogin                        AS NetLogin,
			   AL.LeaveID                         AS LeaveID,
			   AL.dutyProgramId2                  AS dutyProgramId2,
			   AL.dutyProgramId3                  AS dutyProgramId3,
			   AL.dutyProgramId4                  AS dutyProgramId4,
			   AL.dutyProgramId5                  AS dutyProgramId5,
			   AL.dutyProgramId6                  AS dutyProgramId6,
			   AL.isAgreed                        AS IsAgreed,			   
			   AL.IsNeedCovering                  AS IsNeedCovering,
			   AL.ActingFlag                      AS ActingFlag,
			   AL.TotalPlannedDuration            AS TotalPlannedDuration,
			   AL.IsOverrideOver12                AS IsOverrideOver12
		       INTO #TempGetAllocations
	   FROM
		   ( SELECT AL.DutyName,
					ISNULL(AL.Duration,0) AS Duration,
					AL.WeekNumber,
					AL.iDay,
					AL.StartTime,
					AL.EndTime,
					CASE WHEN AL.DutyComments IS NULL THEN 0 ELSE 1 END AS DutyComments,
					CASE WHEN AL.PersonComments IS NULL THEN 0 ELSE 1 END AS PersonComments,
					AL.MarkedOvertime,
					AL.MarkedSickness,
					AL.UnAllocated,
					AL.ID,
					AL.SchedulingTeamId,
					AL.SchedulingPersonID,
					AL.DutyDate,
					CASE WHEN ISNULL(AL.MannualOThours,0) >= 0 AND ISNULL(AL.isAttention,0) = 1 THEN 1
						  WHEN ISNULL(AL.MannualOThours,0) < 0 THEN 0
						  ELSE isAttention 
						  END AS isAttention,
					AL.isRequest,
					AL.dutyBreakTime,
					AL.dutyColorId,
					AP.isPublished,
					AL.IsHomeTeam,
					AL.MarkWiad,
					AL.MarkActual,
					AL.IsActive,
					AL.isEditable,
					AL.MasterDutyId,
					AL.MarkOverTwelve,
					AL.IsUnderElevenBreak,
					AL.IsUnderElevenBreakOverride,	
					AL.DutyTeamID,
					AL.dutyProgramId,
					AL.dutyProgramId2,
					AL.dutyProgramId3,
					AL.dutyProgramId4,
					AL.dutyProgramId5,
					AL.dutyProgramId6,
					case when CHARINDEX('#'+cast(AL.dutyProgramId as varchar)+'#', @TempLabelList, 1) > 0 then 1 
					     when CHARINDEX('#'+cast(AL.DutyProgramId2 as varchar)+'#', @TempLabelList, 1) > 0 then 1 
						 when CHARINDEX('#'+cast(AL.DutyProgramId3 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AL.DutyProgramId4 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AL.DutyProgramId5 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 when CHARINDEX('#'+cast(AL.DutyProgramId6 as varchar)+'#', @TempLabelList, 1) > 0 then 1
						 else 0 end as ActingFlag,
					CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.SchedulingTeamId 
					 THEN 1 ELSE 0 END	IsDutyFromOtherTeam,
				    ISNULL(rft.PaymentTypeShortCode,'')  AS paymenttypename,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( ISNULL( sd.preferredforename,'') = '' ) THEN (
						   sd.forename + ' ' + sd.surname )
						   ELSE ( sd.preferredforename + ' ' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,
				   ISNULL(AL.SortCode,spl.sortcode)            AS sortcode,
				   scp.eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   scp.PartTimeEDP                             AS ContractedHours,
				   TA.NoOfDays                                 AS accdays,
				   scp.manualedp                               AS manualedp,
				   sp.DisplayLastName                          AS DisplayLastName,
				   sp.DisplayFirstName                         AS DisplayFirstName,
				   st.schedulingTeamName                       AS schedulingTeamName,
				   ISNULL(st.signin,0)                         AS IsSigninAllowed,
				   ISNULL(st.signindays,0)                     AS Signindays,
				   case when signin.active is null then 0
				        when AL.StartTime = signin.starttime
					     AND AL.EndTime = signin.endtime then 2
					     else signin.active end       		   AS signin,
				   isnull(signin.inbuilding,0)                 AS inbuilding,
				   case when signin.active = 1 and ( signin.starttime <> al.starttime
						OR signin.endtime <> al.endtime ) then 4
						when signin.active = 1 and signin.inbuilding = 1 then 3
				        when signin.active = 1 and signin.inbuilding <> 1 then 2
						when signin.active = 2  then 1
						else  1 end              AS ImageNameSignin,
				   case when signin.active = 1 and ( signin.starttime <> al.starttime
						OR signin.endtime <> al.endtime ) then 1
						when signin.active = 1 and signin.inbuilding = 1 then 0
				        when signin.active = 1 and signin.inbuilding <> 1 then 0
						else  1 end                            AS ActionNameForSignin,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   WHEN UPPER(AL.DutyName) like '%SICK%' 
						    OR UPPER(AL.DutyName) like '%LEAVE%' 
							OR UPPER(AL.DutyName) like '%ABSENT%' THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#ebebeb'
				        ELSE spl.backgroundcolour END          AS PersonBackgroundColour,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#000000' 
				        ELSE spl.fontcolour END                AS PersonFontColour,
				   sd.staffnumber                              AS staffnumber,
				   scp.costcode                                AS CostCode,
				   sd.staffid                                  AS staffid,
				   cg.TriangleColour                           AS TriangleColour,
				   LA.CountLeave                               AS CountLeave,
				   LA.Approved                                 AS LeaveApproved,
				   LA.Deleted                                  AS LeaveDeleted,
				   LA.ShortNotice                              AS LeaveShortNotice,
				   LA.oversummer                               AS Leaveoversummer,
				   LA.isOK                                     AS LeaveisOK,
				   TR.ShowLock,
				   TR.LockIconColour,
				   TR.ReqCount,
				   CASE WHEN EDP.ID IS NOT NULL 
				        THEN 1 ELSE 0 END                      AS ShowEDPIcon,				   
				   TA.totalduration                            AS WeekDuration,
				   CASE WHEN scp.manualedp = 1 
				        THEN TA.OverTimeHrs
						WHEN scp.manualedp = 0
						THEN TA.totalduration - (ISNULL(scp.PartTimeEDP,0) * 3600 )
						ELSE NULL END                           AS OverTimeHrs,
				   TA.totalplannedduration                      AS TotalPlannedDuration,
				   CAST(TA.currentweek AS VARCHAR)+'/'+CAST(TA.totalweeks AS VARCHAR) AS AccPeriod,
				   CASE WHEN UPPER(AL.DutyName) like '%SICK%' 
						  OR UPPER(AL.DutyName) like '%LEAVE%' THEN 0
				        WHEN ISNULL(AL.DutyTeamID,0) > 0 
					     AND AL.DutyTeamID <> AL.schedulingTeamId THEN 0 
					    WHEN ISNULL(AL.ishometeam,1) = 0 and ISNULL(AL.MarkWIAD,0) = 0 
						 AND ISNULL(AL.MarkActual,0) = 0 
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0 THEN 0
						WHEN ISNULL(AL.ishometeam,1) = 1 
						 AND ( ISNULL(AL.MarkWIAD,0) = 1 OR ISNULL(AL.MarkActual,0) = 1 ) THEN 0
						WHEN NOT(al.DutyDate between spl.StartDate and spl.EndDate) THEN 0
						ELSE 1 END          AS EditDuty,
				   CASE WHEN AL.IsHomeTeam = 0 THEN 0
				        WHEN AL.isHometeam = 1 AND AL.DutyName <> 'U' 
				         AND AL.MarkWIAD = 0 AND AL.MarkActual = 0 THEN 0 
						WHEN NOT(al.DutyDate between spl.StartDate and spl.EndDate) THEN 0 
						 ELSE 1 END AS ShowWIAD,				   
				   CASE WHEN AL.DutyName NOT like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 ) THEN 2
					    WHEN AL.DutyName like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 )  THEN 1
						ELSE 0 END AS contextMenuClsName,
				   CASE WHEN ISNULL(AL.schedulingpersonid,0) = 0 THEN 'UL'
				              ELSE 'AL' END                    AS DisplayGrid,
				   CASE WHEN ( @NoOfWeeks <= 2 AND ISNULL(TA.totalweeks,0) = 0 AND ISNULL(AL.SchedulingPersonID,0) > 0 ) THEN 
				        SUM( CASE WHEN ISNULL(markwiad,0)=1 THEN 0 
								  ELSE ISNULL(AL.duration,0)-ISNULL(AL.dutyBreakTime,0) END) over (PARTITION BY AL.WeekNumber, AL.SchedulingPersonID) 
				   ELSE 0 end as CurrWeekDuration,
				   CASE WHEN ( @NoOfWeeks <= 2 AND ISNULL(TA.totalweeks,0) = 0 AND ISNULL(AL.SchedulingPersonID,0) > 0 ) THEN 
				        SUM( case when isnull(markwiad,0)=1 then 0 
					              when isnull(markwiad,0)=0 AND isnull(al.duration,0) > 0 THEN 1 END ) over (PARTITION BY AL.WeekNumber, AL.SchedulingPersonID) 
				   ELSE 0 end as CurrWeekNoOfDays,
				   CASE WHEN AL.SchedulingPersonID > 0 AND spl.teamid IS NULL THEN 1 ELSE 0 END AS IsEpiredUSer,
			       ISNULL(LA.LeaveStartTime,0)                  AS LeaveStartTime,
			       ISNULL(LA.LeaveEndTime,0)                    AS LeaveEndTime,
				   CASE WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 0
				        THEN 0
						WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 1
						THEN 1
						ELSE  2 END  AS LeavePDL,
				   CASE WHEN SP.FWANotes IS NOT NULL THEN 1 ELSE 0 END As FWANotesFlag,
				   sd.NetLogin,
				   LA.ID LeaveID,
				   LA.IsAgreed,
				   AL.IsNeedCovering,
				   AL.IsOverrideOver12
			  FROM Allocations AS AL (nolock)
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iDay 
		     INNER JOIN Allocations_published_weeks AP (nolock) on AP.WeekNumber = TD.ixYearWeek 
			                                     AND AP.SchedulingTeamId = AL.SchedulingTeamId   	
		     INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  CASE WHEN ISNULL(al.DutyTeamID,0) > 0 
			                                                              THEN al.DutyTeamID 
																		  ELSE AL.SchedulingTeamId END													 
			 LEFT JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid 
			 LEFT JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON spl.scheduledpersonid  = AL.SchedulingPersonID
							  AND spl.teamid = AL.SchedulingTeamId
							  AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
							  AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))
							  AND SPL.scheduledType = 1	
			 LEFT JOIN (  SELECT DISTINCT STL.ScheduledPersonID AS ScheduledPersonID
			                FROM ScheduledPersonTeam_LINK (nolock) AS STL
						   INNER JOIN schedulingTeams ST (nolock) ON ST.schedulingTeamId = STL.TeamID
						   WHERE ST.schedulingTeamName in ('Other BBC', 'Freelancers','Apprentices')
						     AND STL.IsHomeTeam = 1		
							 AND CONVERT(DATETIME,@startDate,101) <=  isnull( STL.enddate, CONVERT(DATETIME,@startDate,101) ) 
							 AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( STL.startdate, CONVERT(DATETIME,@EndDate,101))							 
			            ) FRL ON FRL.scheduledpersonid =  AL.SchedulingPersonID
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid 
			 LEFT JOIN Staffconfig_Processed scp (nolock) ON sd.staffid = scp.staffid
							 AND AL.dutydate BETWEEN ISNULL(scp.startdate,AL.dutydate)
							                AND ISNULL( scp.enddate,AL.dutydate)
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = scp.accgroupid 
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = scp.paymenttypeid 
			LEFT JOIN signin (nolock) ON signin.schedulingpersonid = AL.schedulingpersonid 
							 AND ( signin.iweek = AL.weeknumber AND signin.iday = AL.iday )
			LEFT JOIN REF_MasterDutyColours mdc (nolock) ON mdc.masterdutycolourid = AL.dutycolorid  
			LEFT JOIN LeaveApplications LA  (nolock) ON TD.dDateTime = LA.dDate  AND LA.schedulingpersonid = AL.schedulingpersonid
			                                        AND LA.Deleted = 0 
			LEFT JOIN @TempCharging CG ON  AL.ID = CG.AllocationId AND AL.MasterDutyId = CG.MasterDutyId
            LEFT JOIN @TempAccounting TA ON TA.WeekNumber = TD.ixYearWeek AND TA.schedulingpersonid = AL.schedulingpersonid	
            LEFT JOIN @TempRequest TR ON TR.WeekNumber = TD.ixYearWeek AND TR.iDay= TD.ixDayInWeek AND TR.schedulingpersonid = AL.schedulingpersonid
		    LEFT JOIN EDP (nolock) ON EDP.SchedulingPersonID = AL.SchedulingPersonID
				  AND EDP.ddate = TD.dDateTime 
		     WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			   AND AL.SchedulingTeamId = @pteamId	
			   AND 1 = CASE WHEN @AllocDataFlag = 1 THEN 1 ELSE 0 END
		) AL LEFT JOIN
		(
			select schedulingpersonid, 
			       weeknumber, 
				   iday,
				   min(isapproved) isapproved,
				   max(breachtype) maxbreachtype
			FROM (
			SELECT wtd.schedulingTeamId, 
			       wtd.schedulingpersonid, 
				   wtd.breachtype, 
				   td.ixYearWeek weeknumber, 
				   td.ixDayInWeek iday, 
				   wtd.isapproved
			  FROM working_time_directive WTD (nolock)
			 INNER JOIN TimeDimension TD (nolock) ON td.dDateTime between wtd.StartDate and wtd.enddate
			 WHERE StartDate <= @EndDate 
			   AND EndDate >= @StartDate
			   AND wtd.isapproved <> 2
			   AND WTD.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
						 then @SchedulingPersonID 
						 else WTD.SchedulingPersonID 
						 END
               AND 1 = CASE WHEN @ShowOnlyUnAllocatedDuty = 1 THEN 2 ELSE 1 END
			  ) FD GROUP BY schedulingpersonid, weeknumber, iday
		) WTD ON WTD.schedulingpersonid = AL.schedulingpersonid 
		     and WTD.weeknumber = AL.WeekNumber
			 AND WTD.iday = AL.iday
		   WHERE AL.IsEpiredUSer = 0

	 IF ( @ArchiveDataFlag = 1)
	  BEGIN
		INSERT INTO #TempGetAllocations
	    SELECT al.staffnumber                     AS StaffNumber,
			   al.dutyname                        AS DutyName,
			   al.duration                        AS Duration,
			   al.weeknumber                      AS WeekNumber,
			   al.iday                            AS iDay,
			   al.starttime                       AS StartTime,
			   al.endtime                         AS EndTime,
			   al.sortcode                        AS SortCode,
			   al.dutycomments                    AS DutyComments,
			   al.personcomments                  AS PersonComments,
			   al.markedovertime                  AS MarkedOvertime,
			   al.markedsickness                  AS MarkedSickness,
			   al.unallocated                     AS UnAllocated,
			   al.id                              AS ID,
			   al.schedulingteamid                AS SchedulingTeamId,
			   al.schedulingpersonid              AS SchedulingPersonID,
			   FORMAT(al.dutydate, 'yyyy-MM-dd')  AS DutyDate,
			   al.ispublished                     AS isPublished,
			   al.ishometeam                      AS IsHomeTeam,
			   al.markwiad                        AS MarkWiad,
			   al.markactual                      AS MarkActual,
			   al.isattention                     AS isAttentionClsName,
			   al.isrequest                       AS isRequest,
			   al.dutybreaktime                   AS dutyBreakTime,
			   al.dutycolorid                     AS dutyColorId,
			   al.mASterdutyid                    AS MasterDutyId,
			   al.isactive                        AS isActive,
			   al.iseditable                      AS isEditable,
			   al.paymenttypename                 AS pay,
			   al.displayname                     AS DisplayName,
			   al.DisplayLastName                 AS DisplayLastName,
			   al.DisplayFirstName                AS DisplayFirstName,
			   al.schedulingTeamName              AS schedulingTeamName,
			   al.IsSigninAllowed                 AS IsSigninAllowed,
			   al.Signindays                      AS Signindays,			   
			   al.eft                             AS EFT,
			   al.acc                             AS ACC,
			   al.contractedhours                 AS ContractedHours,
			   CASE WHEN al.accdays IS NULL THEN  AL.CurrWeekNoOfDays
			        ELSE al.accdays END           AS AccDays,
			   al.OverTimeHrs                     AS OverTimeHrs,
			   al.manualedp                       AS ManualEDP,
			   al.signin                          AS signin,
			   al.inbuilding                      AS inbuilding,
               al.ActionNameForSignin             AS ActionNameForSignin,
			   al.ImageNameSignin                 AS ImageNameSignin,			   
			   al.colourbackground                AS ColourBackground,
			   al.colourfont                      AS ColourFont,
			   al.personbackgroundcolour          AS PersonBackgroundColour,
			   al.personfontcolour                AS PersonFontColour,
			   CASE WHEN AL.WeekDuration is null then 
			             CASE WHEN AL.CurrWeekDuration = 0 THEN '00.00'
						      ELSE CAST(AL.CurrWeekDuration AS VARCHAR)
                         END							  
					ELSE CAST(AL.WeekDuration AS VARCHAR) END   AS WeekDuration,
			   AL.AccPeriod                       AS AccPeriod,
			   al.CostCode                        AS CostCode,
			   al.staffid                         AS StaffID,
			   al.TriangleColour                  AS TriangleColour,
			   AL.CountLeave                      AS CountLeave,
			   AL.LeaveApproved                   AS LeaveApproved,
			   AL.LeaveDeleted                    AS LeaveDeleted,
			   AL.LeaveShortNotice                AS LeaveShortNotice,
			   AL.Leaveoversummer                 AS Leaveoversummer,
			   AL.LeaveisOK                       AS LeaveisOK,
			   AL.EditDuty                        AS EditDuty,
			   AL.ShowLock,
			   AL.LockIconColour,
			   AL.ReqCount,
			   AL.ShowEDPIcon                     AS ShowEDPIcon,
			   AL.ShowWIAD                        AS ShowWIAD,
			   CASE WHEN WTD.isapproved = 0 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 1
				    WHEN WTD.isapproved = 1 AND WTD.maxbreachtype <> 1 AND AL.DutyName <> 'U' THEN 2
					WHEN WTD.isapproved = 0 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 1
				    WHEN WTD.isapproved = 1 AND AL.DutyName NOT IN ('Leave','-','OFF Leave','U') AND WTD.maxbreachtype = 1 THEN 2
				 ELSE 0
			    END                               AS WTDBreachClassName,
			   AL.contextMenuClsName              AS contextMenuClsName,
			   AL.DisplayGrid                     AS DisplayGrid,
			   AL.MarkOverTwelve                  AS MarkOverTwelve,
			   AL.IsUnderElevenBreak              AS IsUnderElevenBreak,
			   AL.IsUnderElevenBreakOverride      AS IsUnderElevenBreakOverride,
			   AL.DutyTeamID 			          AS DutyTeamID,
			   AL.IsDutyFromOtherTeam             AS IsDutyFromOtherTeam,
			   AL.dutyProgramId                   AS dutyProgramId,
			   count(AL.DutyName) over ( partition by AL.dutydate, AL.dutyname,AL.schedulingpersonid) AS DutyInstances,
			   AL.LeaveStartTime                  AS LeaveStartTime,
			   AL.LeaveEndTime                    AS LeaveEndTime,
			   AL.LeavePDL                        AS LeavePDL,
			   AL.FWANotesFlag                    AS FWANotesFlag,
			   AL.NetLogin                        AS NetLogin,
			   AL.LeaveID                         AS LeaveID,
			   AL.dutyProgramId2                  AS dutyProgramId2,
			   AL.dutyProgramId3                  AS dutyProgramId3,
			   AL.dutyProgramId4                  AS dutyProgramId4,
			   AL.dutyProgramId5                  AS dutyProgramId5,
			   AL.dutyProgramId6                  AS dutyProgramId6,
			   AL.isAgreed                        AS IsAgreed,		   
			   AL.IsNeedCovering                  AS IsNeedCovering,
			   AL.TotalPlannedDuration            AS TotalPlannedDuration,
			   AL.IsOverrideOver12                AS IsOverrideOver12
	   FROM
		   ( SELECT AL.DutyName,
					ISNULL(AL.Duration,0) AS Duration,
					AL.WeekNumber,
					AL.iDay,
					AL.StartTime,
					AL.EndTime,
					CASE WHEN AL.DutyComments IS NULL THEN 0 ELSE 1 END AS DutyComments,
					CASE WHEN AL.PersonComments IS NULL THEN 0 ELSE 1 END AS PersonComments,
					AL.MarkedOvertime,
					AL.MarkedSickness,
					AL.UnAllocated,
					AL.ID,
					AL.SchedulingTeamId,
					AL.SchedulingPersonID,
					AL.DutyDate,
					CASE WHEN ISNULL(AL.MannualOThours,0) >= 0 AND ISNULL(AL.isAttention,0) = 1 THEN 1
						  WHEN ISNULL(AL.MannualOThours,0) < 0 THEN 0
						  ELSE isAttention 
						  END AS isAttention,
					AL.isRequest,
					AL.dutyBreakTime,
					AL.dutyColorId,
					AP.isPublished,
					AL.IsHomeTeam,
					AL.MarkWiad,
					AL.MarkActual,
					AL.IsActive,
					AL.isEditable,
					AL.MasterDutyId,
					AL.MarkOverTwelve,
					AL.IsUnderElevenBreak,
					AL.IsUnderElevenBreakOverride,	
					AL.DutyTeamID,
					AL.dutyProgramId,
					AL.dutyProgramId2,
					AL.dutyProgramId3,
					AL.dutyProgramId4,
					AL.dutyProgramId5,
					AL.dutyProgramId6,
					CASE WHEN ISNULL(AL.DutyTeamID,0) > 0 AND AL.DutyTeamID <> AL.SchedulingTeamId 
					 THEN 1 ELSE 0 END	IsDutyFromOtherTeam,
				    ISNULL(rft.PaymentTypeShortCode,'')  AS paymenttypename,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( ISNULL( sd.preferredforename,'') = '' ) THEN (
						   sd.forename + ' ' + sd.surname )
						   ELSE ( sd.preferredforename + ' ' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,
				   ISNULL(AL.SortCode,spl.sortcode)            AS sortcode,
				   scp.eft                                     AS EFT,
				   ag.accgroup                                 AS ACC,
				   scp.PartTimeEDP                             AS ContractedHours,
				   TA.NoOfDays                                 AS accdays,
				   scp.manualedp                               AS manualedp,
				   sp.DisplayLastName                          AS DisplayLastName,
				   sp.DisplayFirstName                         AS DisplayFirstName,
				   st.schedulingTeamName                       AS schedulingTeamName,
				   ISNULL(st.signin,0)                         AS IsSigninAllowed,
				   ISNULL(st.signindays,0)                     AS Signindays,
				   case when signin.active is null then 0
				        when AL.StartTime = signin.starttime
					     AND AL.EndTime = signin.endtime then 2
					     else signin.active end       		   AS signin,
				   isnull(signin.inbuilding,0)                 AS inbuilding,
				   case when signin.active = 1 and ( signin.starttime <> al.starttime
						OR signin.endtime <> al.endtime ) then 4
						when signin.active = 1 and signin.inbuilding = 1 then 3
				        when signin.active = 1 and signin.inbuilding <> 1 then 2
						when signin.active = 2  then 1
						else  1 end              AS ImageNameSignin,
				   case when signin.active = 1 and ( signin.starttime <> al.starttime
						OR signin.endtime <> al.endtime ) then 1
						when signin.active = 1 and signin.inbuilding = 1 then 0
				        when signin.active = 1 and signin.inbuilding <> 1 then 0
						else  1 end                            AS ActionNameForSignin,
				   ( CASE
					   WHEN mdc.colourbackground IS NULL THEN ''
					   WHEN UPPER(AL.DutyName) like '%SICK%' 
						    OR UPPER(AL.DutyName) like '%LEAVE%' 
							OR UPPER(AL.DutyName) like '%ABSENT%' THEN ''
					   ELSE mdc.colourbackground
					 END )                                     AS ColourBackground,
				   ( CASE
					   WHEN mdc.colourfont IS NULL THEN ''
					   ELSE mdc.colourfont
					 END )                                     AS ColourFont,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#ebebeb'
				        ELSE spl.backgroundcolour END          AS PersonBackgroundColour,
				   CASE WHEN spl.IsDefaultBGColour = 1 THEN '#000000' 
				        ELSE spl.fontcolour END                AS PersonFontColour,
				   sd.staffnumber                              AS staffnumber,
				   scp.costcode                                AS CostCode,
				   sd.staffid                                  AS staffid,
				   cg.TriangleColour                           AS TriangleColour,
				   LA.CountLeave                               AS CountLeave,
				   LA.Approved                                 AS LeaveApproved,
				   LA.Deleted                                  AS LeaveDeleted,
				   LA.ShortNotice                              AS LeaveShortNotice,
				   LA.oversummer                               AS Leaveoversummer,
				   LA.isOK                                     AS LeaveisOK,
				   TR.ShowLock,
				   TR.LockIconColour,
				   TR.ReqCount,
				   CASE WHEN EDP.ID IS NOT NULL 
				        THEN 1 ELSE 0 END                      AS ShowEDPIcon,				   
				   TA.totalduration                            AS WeekDuration,
				   CASE WHEN scp.manualedp = 1 
				        THEN TA.OverTimeHrs
						WHEN scp.manualedp = 0
						THEN TA.totalduration - (ISNULL(scp.PartTimeEDP,0) * 3600 )
						ELSE NULL END                           AS OverTimeHrs,
				   TA.totalplannedduration                      AS TotalPlannedDuration,
				   CAST(TA.currentweek AS VARCHAR)+'/'+CAST(TA.totalweeks AS VARCHAR) AS AccPeriod,
				   CASE WHEN UPPER(AL.DutyName) like '%SICK%' 
						  OR UPPER(AL.DutyName) like '%LEAVE%' THEN 0
				        WHEN ISNULL(AL.DutyTeamID,0) > 0 
					     AND AL.DutyTeamID <> AL.schedulingTeamId THEN 0 
					    WHEN ISNULL(AL.ishometeam,1) = 0 and ISNULL(AL.MarkWIAD,0) = 0 
						 AND ISNULL(AL.MarkActual,0) = 0 
						 AND ISNULL(FRL.ScheduledPersonID,0) = 0 THEN 0
						WHEN ISNULL(AL.ishometeam,1) = 1 
						 AND ( ISNULL(AL.MarkWIAD,0) = 1 OR ISNULL(AL.MarkActual,0) = 1 ) THEN 0
						WHEN NOT(al.DutyDate between spl.StartDate and spl.EndDate) THEN 0
						ELSE 1 END          AS EditDuty,
				   CASE WHEN AL.IsHomeTeam = 0 THEN 0
				        WHEN AL.isHometeam = 1 AND AL.DutyName <> 'U' 
				         AND AL.MarkWIAD = 0 AND AL.MarkActual = 0 THEN 0 
						WHEN NOT(al.DutyDate between spl.StartDate and spl.EndDate) THEN 0 
						 ELSE 1 END AS ShowWIAD,				   
				   CASE WHEN AL.DutyName NOT like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 ) THEN 2
					    WHEN AL.DutyName like '%Leave%' AND (AL.IsHomeTeam = 1 OR AL.MarkWiad = 1 OR AL.MarkActual = 1 )  THEN 1
						ELSE 0 END AS contextMenuClsName,
				   CASE WHEN ISNULL(AL.schedulingpersonid,0) = 0 THEN 'UL'
				              ELSE 'AL' END                    AS DisplayGrid,
				   CASE WHEN ( @NoOfWeeks <= 2 AND ISNULL(TA.totalweeks,0) = 0 AND ISNULL(AL.SchedulingPersonID,0) > 0 ) THEN 
				        SUM( CASE WHEN ISNULL(markwiad,0)=1 THEN 0 
								  ELSE ISNULL(AL.duration,0)-ISNULL(AL.dutyBreakTime,0) END) over (PARTITION BY AL.WeekNumber, AL.SchedulingPersonID) 
				   ELSE 0 end as CurrWeekDuration,
				   CASE WHEN ( @NoOfWeeks <= 2 AND ISNULL(TA.totalweeks,0) = 0 AND ISNULL(AL.SchedulingPersonID,0) > 0 ) THEN 
				        SUM( case when isnull(markwiad,0)=1 then 0 
					              when isnull(markwiad,0)=0 AND isnull(al.duration,0) > 0 THEN 1 END ) over (PARTITION BY AL.WeekNumber, AL.SchedulingPersonID) 
				   ELSE 0 end as CurrWeekNoOfDays,
				   CASE WHEN AL.SchedulingPersonID > 0 AND spl.teamid IS NULL THEN 1 ELSE 0 END AS IsEpiredUSer,
			       ISNULL(LA.LeaveStartTime,0)                  AS LeaveStartTime,
			       ISNULL(LA.LeaveEndTime,0)                    AS LeaveEndTime,
				   CASE WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 0
				        THEN 0
						WHEN ( ISNULL(LA.LeaveStartTime,0) > 0 OR ISNULL(LA.LeaveEndTime,0) > 0 ) AND LA.Approved = 1
						THEN 1
						ELSE  2 END  AS LeavePDL,
				   CASE WHEN SP.FWANotes IS NOT NULL THEN 1 ELSE 0 END As FWANotesFlag,
				   sd.NetLogin,
				   LA.ID LeaveID,
				   LA.IsAgreed,
				   AL.IsNeedCovering,
				   AL.IsOverrideOver12
			  FROM Allocations_Archive AS AL (nolock)
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL.WeekNumber and TD.ixDayInWeek = AL.iDay 
		     INNER JOIN Allocations_published_weeks AP (nolock) on AP.WeekNumber = TD.ixYearWeek 
			                                     AND AP.SchedulingTeamId = AL.SchedulingTeamId   	
		     INNER JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =  CASE WHEN ISNULL(al.DutyTeamID,0) > 0 
			                                                              THEN al.DutyTeamID 
																		  ELSE AL.SchedulingTeamId END													 
			 LEFT JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid 
			 LEFT JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON spl.scheduledpersonid  = AL.SchedulingPersonID
							  AND spl.teamid = AL.SchedulingTeamId
							  AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
							  AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))
							  AND SPL.scheduledType = 1	
			 LEFT JOIN (  SELECT DISTINCT STL.ScheduledPersonID AS ScheduledPersonID
			                FROM ScheduledPersonTeam_LINK (nolock) AS STL
						   INNER JOIN schedulingTeams ST (nolock) ON ST.schedulingTeamId = STL.TeamID
						   WHERE ST.schedulingTeamName in ('Other BBC', 'Freelancers','Apprentices')
						     AND STL.IsHomeTeam = 1		
							 AND CONVERT(DATETIME,@startDate,101) <=  isnull( STL.enddate, CONVERT(DATETIME,@startDate,101) ) 
							 AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( STL.startdate, CONVERT(DATETIME,@EndDate,101))							 
			            ) FRL ON FRL.scheduledpersonid =  AL.SchedulingPersonID
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid 
			 LEFT JOIN Staffconfig_Processed scp (nolock) ON sd.staffid = scp.staffid
							 AND AL.dutydate BETWEEN ISNULL(scp.startdate,AL.dutydate)
							                AND ISNULL( scp.enddate,AL.dutydate)
			LEFT JOIN AccountingGroups AS ag (nolock) ON ag.id = scp.accgroupid 
			LEFT JOIN REF_PaymentType AS rft (nolock) ON rft.paymenttypeid = scp.paymenttypeid 
			LEFT JOIN signin (nolock) ON signin.schedulingpersonid = AL.schedulingpersonid 
							 AND ( signin.iweek = AL.weeknumber AND signin.iday = AL.iday )
			LEFT JOIN REF_MasterDutyColours mdc (nolock) ON mdc.masterdutycolourid = AL.dutycolorid  
			LEFT JOIN LeaveApplications LA  (nolock) ON TD.dDateTime = LA.dDate  AND LA.schedulingpersonid = AL.schedulingpersonid
			                                        AND LA.Deleted = 0 
			LEFT JOIN @TempCharging CG ON  AL.ID = CG.AllocationId AND AL.MasterDutyId = CG.MasterDutyId
            LEFT JOIN @TempAccounting TA ON TA.WeekNumber = TD.ixYearWeek AND TA.schedulingpersonid = AL.schedulingpersonid	
            LEFT JOIN @TempRequest TR ON TR.WeekNumber = TD.ixYearWeek AND TR.iDay= TD.ixDayInWeek AND TR.schedulingpersonid = AL.schedulingpersonid
		    LEFT JOIN EDP (nolock) ON EDP.SchedulingPersonID = AL.SchedulingPersonID
				  AND EDP.ddate = TD.dDateTime 
		     WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			   AND AL.SchedulingTeamId = @pteamId	
		) AL LEFT JOIN
		(
			select schedulingpersonid, 
			       weeknumber, 
				   iday,
				   min(isapproved) isapproved,
				   max(breachtype) maxbreachtype
			FROM (
			SELECT wtd.schedulingTeamId, 
			       wtd.schedulingpersonid, 
				   wtd.breachtype, 
				   td.ixYearWeek weeknumber, 
				   td.ixDayInWeek iday, 
				   wtd.isapproved
			  FROM working_time_directive WTD (nolock)
			 INNER JOIN TimeDimension TD (nolock) ON td.dDateTime between wtd.StartDate and wtd.enddate
			 WHERE StartDate <= @EndDate 
			   AND EndDate >= @StartDate
			   AND wtd.isapproved <> 2
			   AND WTD.SchedulingPersonID = case when isnull(@SchedulingPersonID,0) > 0 
						 then @SchedulingPersonID 
						 else WTD.SchedulingPersonID 
						 END
               AND 1 = CASE WHEN @ShowOnlyUnAllocatedDuty = 1 THEN 2 ELSE 1 END
			  ) FD GROUP BY schedulingpersonid, weeknumber, iday
		) WTD ON WTD.schedulingpersonid = AL.schedulingpersonid 
		     and WTD.weeknumber = AL.WeekNumber
			 AND WTD.iday = AL.iday
		   WHERE AL.IsEpiredUSer = 0

	  END

			
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
			SET @filterOrderCond = replace( @filterOrderCond, 'a.dutyProgramId','dutyProgramId')				
         END		
				
		IF ( ISNULL(@filterCond,'') = '')
		  BEGIN
		  
		   IF ( ISNULL(@DutyFilterID,0) > 0 )
		    BEGIN

			   SET @FilterSQL = ' SELECT * FROM #TempGetAllocations 
		                              WHERE ISNULL(SchedulingPersonID,0) = 0 
										AND ISNULL(isActive,1) = 1 
										AND DutyName IN (SELECT DISTINCT md.DutyName 
					                                       FROM MasterDutiesFilterLinks mdfl
														   JOIN MasterDuties md ON md.MasterDutyID = mdfl.MasterDutyID
														  WHERE mdfl.FilterID = '+ cast(@DutyFilterID AS VARCHAR)+ ') '
                
			   IF ( @ShowOnlyUnAllocatedDuty <> 1 )
				BEGIN
				 SET @FilterSQL = @FilterSQL+'  UNION ALL SELECT * FROM #TempGetAllocations '
												
					IF ( ISNULL(@SchedulingPersonID,0) > 0 )
					 BEGIN
					  SET @FilterSQL = @FilterSQL+' WHERE  schedulingpersonid = '+CAST(@SchedulingPersonID AS VARCHAR) +' '  
					 END												
					ELSE
                     BEGIN					
					  SET @FilterSQL = @FilterSQL+'	WHERE schedulingpersonid > 0 '
					 END
					  
				END 
			
			END
		   ELSE
		    BEGIN
			   SET @FilterSQL = 'SELECT * FROM #TempGetAllocations 
								 WHERE DutyName is not null 
								   AND ISNULL(isActive,1) = CASE when ISNULL(schedulingpersonid,0) = 0 THEN 1 ELSE ISNULL(isActive,1) END '	
								   
				IF ( ISNULL(@SchedulingPersonID,0) > 0 AND ISNULL(@ShowUnAllocatedDuty,0) = 0 )
				 BEGIN
				  SET @FilterSQL = @FilterSQL+' AND schedulingpersonid = '+ CAST(@SchedulingPersonID AS VARCHAR)
				 END
				 
				IF ( ISNULL(@SchedulingPersonID,0) > 0 AND ISNULL(@ShowUnAllocatedDuty,0) = 1 )
				 BEGIN
				  SET @FilterSQL = @FilterSQL+' AND ( schedulingpersonid = '+CAST(@SchedulingPersonID AS VARCHAR) +' OR ISNULL(schedulingpersonid,0) = 0 ) '
				 END

				IF ( @ShowOnlyUnAllocatedDuty = 1 )
				 BEGIN
				  SET @FilterSQL = @FilterSQL+' AND ISNULL(schedulingpersonid,0) = 0 '
				 END		

				IF ( @ShowUnAllocatedDuty = 2 AND ISNULL(@SchedulingPersonID,0) = 0  )
				 BEGIN
				  SET @FilterSQL = @FilterSQL+' AND ISNULL(schedulingpersonid,0) > 0 '
				 END

				IF ( @ShowUnAllocatedDuty = 2 AND ISNULL(@SchedulingPersonID,0) > 0  )
				 BEGIN
				  SET @FilterSQL = @FilterSQL+' AND schedulingpersonid = '+ CAST(@SchedulingPersonID AS VARCHAR)
				 END			 
							
				 SET @FilterSQL = @FilterSQL + ISNULL(@filterOrderCond,' ')
				 
            END
		  END
		ELSE
		 BEGIN
		   		  
			  SET @VFilter = replace( @filterCond, 'sp.DisplayName', 'DisplayName')
			  SET @VFilter = replace( @VFilter, 'spl.SortCode', 'sortcode')
			  SET @VFilter = replace( @VFilter, 'a.DutyName', 'DutyName')
			  SET @VFilter = replace( @VFilter, 'sct.CostCode','CostCode') 
			  SET @VFilter = replace( @VFilter, 'a.StartTime','StartTime')
			  SET @VFilter = replace( @VFilter, 'a.dutyProgramId','dutyProgramId')			  
			  		  
			  IF ( CHARINDEX ('aj.JobName',@VFilter) > 0 OR CHARINDEX ('aj.ProgrammeId',@VFilter) > 0 )
			   BEGIN
			     
				 SET @vleftjoinflag = 1				

					  select AJ.AllocationID, AJ.JobName, AJ.ProgrammeID
							 INTO #TempJobs
					  from Allocations_Jobs AJ
					  INNER JOIN #TempGetAllocations AL ON AL.ID= AJ.AllocationID
					  WHERE 1 = CASE WHEN @AllocDataFlag = 1 THEN 1 ELSE 2 END

                
				 IF ( @ArchiveDataFlag = 1 )
				  BEGIN
					 INSERT INTO #TempJobs
					 select AJ.AllocationID, AJ.JobName, AJ.ProgrammeID					   
					  from Allocations_Jobs AJ
					  INNER JOIN #TempGetAllocations AL ON AL.ID= AJ.AllocationID
				  END
				  
                  SET @VLJFilter = ' INNER JOIN #TempJobs ON #TempGetAllocations.ID=#TempJobs.AllocationID '
                                     									 
			  
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
					
                  SET @VLJFilter = ISNULL(@VLJFilter,'') + ' LEFT JOIN #TempStaffSkills  ON #TempGetAllocations.ID=#TempStaffSkills.ID '
									 
			   END				  
		  
			  SET @VFilter = replace( @VFilter, 'spsl.programmes_id','programmes_id') 	
			  
			  IF (@vleftjoinflag = 1)
			    BEGIN

				   IF ( ISNULL(@SkillFilters,'') <> '' OR ISNULL(@DutyLabelFilters,'') <> '' ) 
				    BEGIN

					  SELECT @NoOfSkills = count(VALUE) FROM string_split(@SkillFilters,',')
					  SELECT @NoOfLabels = count(VALUE) FROM string_split(@DutyLabelFilters,',')

				       SET @FilterSQL =  ' SELECT * FROM #TempGetAllocations WHERE schedulingpersonid IN ( 
				                           SELECT schedulingpersonid FROM ( SELECT schedulingpersonid,
										          DENSE_RANK()   over (partition by schedulingpersonid order by '
											  + case when ISNULL(@DutyLabelFilters,'') <> '' and ISNULL(@SkillFilters,'') = '' THEN ' dutyProgramId '
											         when ISNULL(@DutyLabelFilters,'') = '' and ISNULL(@SkillFilters,'') <> '' THEN ' programmes_id '
												 END 
											  +') as rownum
										      FROM #TempGetAllocations  '
								    +@VLJFilter+' WHERE 1=1  '+@VFilter
									+ case when ISNULL(@DutyLabelFilters,'') <> '' and ISNULL(@SkillFilters,'') = '' 
									        THEN ' AND dutyProgramId IN ('+@DutyLabelFilters+')'
										   when ISNULL(@DutyLabelFilters,'') = '' and ISNULL(@SkillFilters,'') <> '' 
										    THEN ' AND programmes_id IN ('+@SkillFilters+')'
										END 
									+ ' ) FD WHERE rownum  >= '
									+ case when ISNULL(@DutyLabelFilters,'') <> '' and ISNULL(@SkillFilters,'') = '' THEN cast(@NoOfLabels as varchar)
										   when ISNULL(@DutyLabelFilters,'') = '' and ISNULL(@SkillFilters,'') <> '' THEN cast(@NoOfSkills as varchar)
										END 
									+' ) and schedulingpersonid > 0 '+@filterOrderCond

					 IF ( ISNULL(@SkillFilters,'') <> '' AND ISNULL(@DutyLabelFilters,'') <> '' ) 
				      BEGIN

					   SET @IsAndFilter = CHARINDEX( 'AND',@VFilter,4)

					   IF ( @IsAndFilter = 0 )
						BEGIN
						   SET @FilterSQL =  ' SELECT * FROM #TempGetAllocations WHERE schedulingpersonid IN (
								 SELECT schedulingpersonid 
								  FROM ( SELECT schedulingpersonid,
												DENSE_RANK()   over (partition by schedulingpersonid order by  dutyProgramId ) as rownum
										   FROM #TempGetAllocations '
										+@VLJFilter+' WHERE 1=1 '+@VFilter									
										+' AND dutyProgramId IN ('+@DutyLabelFilters+') ) FD WHERE rownum  >= '+ cast(@NoOfLabels as varchar) 
								 + ' UNION 
								  SELECT schedulingpersonid 
									FROM ( SELECT schedulingpersonid,
												 DENSE_RANK()   over (partition by schedulingpersonid order by programmes_id ) as rownum
												  FROM #TempGetAllocations  '
										+@VLJFilter+' WHERE 1=1 '+@VFilter
										+' AND programmes_id IN ('+@SkillFilters+') ) FD WHERE rownum  >= '+ cast(@NoOfSkills as varchar)
								+' ) and schedulingpersonid > 0 '+@filterOrderCond
					    END
					   ELSE
						BEGIN
						   SET @FilterSQL =  ' SELECT * FROM #TempGetAllocations WHERE schedulingpersonid IN (
								 SELECT schedulingpersonid 
								  FROM ( SELECT schedulingpersonid,
												DENSE_RANK()   over (partition by schedulingpersonid order by  dutyProgramId ) as rownum
										   FROM #TempGetAllocations '
										+@VLJFilter+' WHERE 1=1 '+@VFilter									
										+' AND dutyProgramId IN ('+@DutyLabelFilters+') ) FD WHERE rownum  >= '+ cast(@NoOfLabels as varchar) 
								 + ' INTERSECT 
								  SELECT schedulingpersonid 
									FROM ( SELECT schedulingpersonid,
												 DENSE_RANK()   over (partition by schedulingpersonid order by programmes_id ) as rownum
												  FROM #TempGetAllocations  '
										+@VLJFilter+' WHERE 1 = 1 '+@VFilter
										+' AND programmes_id IN ('+@SkillFilters+') ) FD WHERE rownum  >= '+ cast(@NoOfSkills as varchar)
								+' ) and schedulingpersonid > 0 '+@filterOrderCond
					    END													
					  END

					END
                   ELSE
				    BEGIN			  
				       SET @FilterSQL =  ' SELECT * FROM #TempGetAllocations WHERE schedulingpersonid IN ( 
				                           SELECT tg.schedulingpersonid FROM #TempGetAllocations TG '
								    +@VLJFilter+' WHERE 1=1 '+@VFilter+' ) and schedulingpersonid > 0 '+@filterOrderCond
				    END
				
				END
			  ELSE
			    BEGIN

				   IF ( ISNULL(@DutyLabelFilters,'') <> '' ) 
				    BEGIN

					  SELECT @NoOfLabels = count(VALUE) FROM string_split(@DutyLabelFilters,',')

				       SET @FilterSQL =  'SELECT * FROM #TempGetAllocations where schedulingpersonid in 
				                      (SELECT schedulingpersonid FROM ( SELECT schedulingpersonid,
									          DENSE_RANK()   over (partition by schedulingpersonid order by '
											  + case when ISNULL(@DutyLabelFilters,'') <> '' and ISNULL(@SkillFilters,'') = '' THEN ' dutyProgramId '
												 END 
											  +') as rownum
									   FROM #TempGetAllocations WHERE 1=1  '+ @VFilter 
									 +' ) FD WHERE  rownum >= '
									 + CASE when ISNULL(@DutyLabelFilters,'') <> '' and ISNULL(@SkillFilters,'') = '' THEN  cast(@NoOfLabels as varchar)
									        ELSE ' 0 '
									    END
									 +' ) and schedulingpersonid > 0 '+@filterOrderCond
				   END					   
				  ELSE 
				   BEGIN 				  				   
				       SET @FilterSQL =  'SELECT * FROM #TempGetAllocations where schedulingpersonid in 
				                     ( SELECT schedulingpersonid FROM #TempGetAllocations WHERE 1=1  '+ @VFilter
									 +') and schedulingpersonid > 0 '+@filterOrderCond
				   END
				END
			  
		  END	  				
	  END
	  
         EXEC ( @FilterSQL )	

END