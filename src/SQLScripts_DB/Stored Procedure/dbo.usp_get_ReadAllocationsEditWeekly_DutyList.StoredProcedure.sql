USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsEditWeekly_DutyList]    Script Date: 30/05/2024 19:15:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER          PROCEDURE  [dbo].[usp_get_ReadAllocationsEditWeekly_DutyList]
@startDate                 VARCHAR(22),
@EndDate                   VARCHAR(22),
@pteamId			       INT,
@filterCond                VARCHAR(MAX) = NULL,
@filterOrderCond           VARCHAR(MAX) = NULL,
@DutyFilterID              INT = NULL


AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

    DECLARE @FilterSQL                 VARCHAR(MAX)
	DECLARE @VFilter                   VARCHAR(MAX)
	DECLARE @VLJFilter                 VARCHAR(MAX)	
	DECLARE @FilterSetFlag             INT = 0
	DECLARE @vleftjoinflag             INT = 0
		 
	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1	 	 

             SELECT AL.DutyName,
			        AL.MasterDutyId                            AS MasterDutyId,			 
					AL.WeekNumber,
					AL.iDay,
					AL.StartTime,
					AL.EndTime,
					AL.ID,
					AL.SchedulingPersonID,
					FORMAT(AL.DutyDate, 'yyyy-MM-dd')          AS DutyDate,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( ISNULL( sd.preferredforename,'') = '' ) THEN (
						   sd.forename + ' ' + sd.surname )
						   ELSE ( sd.preferredforename + ' ' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,
				   AL.dutyProgramId                            AS dutyProgramId,					 
				   ISNULL(AL.SortCode,spl.sortcode)            AS SortCode,
				   scp.costcode                                AS CostCode,
				   sd.staffid                                  AS StaffID,
				   AL.isActive                                 AS IsActive,
				   CASE WHEN ISNULL(AL.schedulingpersonid,0) = 0 THEN 'UL'
				              ELSE 'AL' END                    AS DisplayGrid,
				   CASE WHEN AL.SchedulingPersonID > 0 AND spl.teamid IS NULL THEN 1 ELSE 0 END AS IsExpiredUSer,
				   case when isnull(al.SchedulingPersonID,0) = 0 then 
				   count(AL.DutyName) over ( partition by AL.dutydate, AL.dutyname,AL.schedulingpersonid)
				   else 1 end AS DutyInstances
		      INTO #TempGetAllocations				   
			  FROM Allocations AS AL (nolock)
	         INNER JOIN Timedimension TD  (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek														 
			 LEFT JOIN ScheduledPeople AS sp (nolock) ON AL.schedulingpersonid  = sp.scheduledpersonid
			 LEFT JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON AL.SchedulingPersonID = spl.scheduledpersonid  
							  AND AL.SchedulingTeamId = spl.teamid
							  AND SPL.scheduledType = 1	
							  AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
							  AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))								  
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid  
			 LEFT JOIN Staffconfig_Processed scp (nolock) ON sd.staffid = scp.staffid
							 AND AL.dutydate BETWEEN ISNULL(scp.startdate,AL.dutydate)
							                AND ISNULL( scp.enddate,AL.dutydate)											
		     WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			   AND AL.SchedulingTeamId = @pteamId			   
			
		IF ( ISNULL(@filterOrderCond,'') <> '')
		 BEGIN
			SET @filterOrderCond = replace( @filterOrderCond, 'sp.DisplayName', 'DisplayName')
			SET @filterOrderCond = replace( @filterOrderCond, 'spl.SortCode', 'sortcode')
			SET @filterOrderCond = replace( @filterOrderCond, 'a.DutyName', 'DutyName')
			SET @filterOrderCond = replace( @filterOrderCond, 'scp.CostCode','CostCode') 		
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

			   SET @FilterSQL = ' SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate,DisplayGrid,DutyInstances
			                        FROM #TempGetAllocations 
		                              WHERE ISNULL(SchedulingPersonID,0) = 0 
										AND ISNULL(isActive,1) = 1 
										AND DutyName IN (SELECT DISTINCT md.DutyName 
					                                       FROM MasterDutiesFilterLinks mdfl (nolock)
														   JOIN MasterDuties md ON md.MasterDutyID = mdfl.MasterDutyID
														  WHERE mdfl.FilterID = '+ cast(@DutyFilterID AS VARCHAR)+ ') 
											UNION ALL
							                    SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate,DisplayGrid,DutyInstances
												FROM #TempGetAllocations 
												WHERE schedulingpersonid > 0 AND DutyName <> ''U'' AND IsExpiredUSer = 0 '
				 SET @FilterSQL = @FilterSQL + ISNULL(@filterOrderCond,' ')												
		    END
		  ELSE
		    BEGIN
		  
			   SET @FilterSQL = 'SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate,DisplayGrid,DutyInstances
			                       FROM #TempGetAllocations 
								  WHERE ISNULL(DutyName,''U'') <> ''U''
								    AND IsExpiredUSer = 0
								    AND ISNULL(isActive,1) = CASE when ISNULL(schedulingpersonid,0) = 0 THEN 1 ELSE ISNULL(isActive,1) END '	
							
				 SET @FilterSQL = @FilterSQL + ISNULL(@filterOrderCond,' ')
			END	 
		  END
		ELSE
		 BEGIN
		   		  
			  SET @VFilter = replace( @filterCond, 'sp.DisplayName', 'DisplayName')
			  SET @VFilter = replace( @VFilter, 'spl.SortCode', 'sortcode')
			  SET @VFilter = replace( @VFilter, 'a.DutyName', 'DutyName')
			  SET @VFilter = replace( @VFilter, 'scp.CostCode','CostCode') 
			  SET @VFilter = replace( @VFilter, 'a.StartTime','StartTime')
			  SET @VFilter = replace( @VFilter, 'a.dutyProgramId','dutyProgramId')			  
			  		  
			  IF ( CHARINDEX ('aj.JobName',@VFilter) > 0 OR CHARINDEX ('aj.ProgrammeId',@VFilter) > 0 )
			   BEGIN
			     
				 SET @vleftjoinflag = 1
				 
                  select AJ.AllocationID, AJ.JobName, AJ.ProgrammeID
				         INTO #TempJobs
				  from Allocations_Jobs AJ (nolock)
				  INNER JOIN #TempGetAllocations AL ON AL.ID= AJ.AllocationID
				  
                  SET @VLJFilter = ' INNER JOIN #TempJobs ON #TempGetAllocations.ID=#TempJobs.AllocationID '
                                     									 
			  
			   END	
			   
			  SET @VFilter = replace( @VFilter, 'aj.JobName', 'JobName')
			  SET @VFilter = replace( @VFilter, 'aj.ProgrammeId','ProgrammeId') 	

			  IF ( CHARINDEX ('spsl.programmes_id',@VFilter) > 0 )
			   BEGIN
			  
			    SET @vleftjoinflag = 1
			  
				  SELECT AL.ID, programmes_id
				         INTO #TempStaffSkills
				    FROM skills_programmes_staff_link spsl (nolock)
					INNER JOIN #TempGetAllocations AL ON spsl.staff_id = AL.StaffID 
					
                  SET @VLJFilter = ISNULL(@VLJFilter,'') + ' INNER JOIN #TempStaffSkills  ON #TempGetAllocations.ID=#TempStaffSkills.ID '
									 
			   END				  
		  
			  SET @VFilter = replace( @VFilter, 'spsl.programmes_id','programmes_id') 	
			  
			  IF (@vleftjoinflag = 1)
			    BEGIN

				  SET @FilterSQL = ' SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate, DisplayGrid,DutyInstances 
									  FROM #TempGetAllocations WHERE IsExpiredUSer = 0 AND SchedulingPersonID IN ( 
				                           SELECT TG.SchedulingPersonID FROM #TempGetAllocations TG '
								    +@VLJFilter +@VFilter+' ) '+@filterOrderCond	
									+' union 
									SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate,DisplayGrid,DutyInstances 
			                       FROM #TempGetAllocations 
								  WHERE ISNULL(DutyName,''U'') <> ''U''
								    AND IsExpiredUSer = 0
								    AND ISNULL(isActive,1) =  1 
									AND ISNULL(schedulingpersonid,0) = 0 '
				
				END
			  ELSE
			    BEGIN
				  
				  SET @FilterSQL = ' SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate, DisplayGrid,DutyInstances 
									 FROM #TempGetAllocations WHERE IsExpiredUSer = 0  and schedulingpersonid IN 
									 ( SELECT schedulingpersonid FROM #TempGetAllocations WHERE 1=1 '+ @VFilter+' ) '+@filterOrderCond
									+' union 
									SELECT DutyName, MasterDutyId,	WeekNumber, iDay,StartTime, EndTime,ID, SchedulingPersonID, DutyDate,DisplayGrid,DutyInstances 
			                       FROM #TempGetAllocations 
								  WHERE ISNULL(DutyName,''U'') <> ''U''
								    AND IsExpiredUSer = 0
								    AND ISNULL(isActive,1) =  1 
									AND ISNULL(schedulingpersonid,0) = 0 '

				
				END
			  
		  END	  

         EXEC ( @FilterSQL )
		
END