USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveTakenSummary]    Script Date: 12/05/2022 19:08:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_LeaveTakenSummary] 
	-- Add the parameters for the stored procedure here
	@intTeamID int,
	@startdate varchar (50),
	@enddate varchar (50)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

   		

SELECT          Leave.StaffNumber, SUM(Leave.Annual) AS Annual, SUM(Leave.PHL) AS PHL, SUM(Leave.Comp) AS Comp, SUM(Leave.Additional) AS Additional,
                               SUM(cast(ISNULL(Leave.Exceptional, 0) as float)) AS Exceptional, 
                               SUM(cast(ISNULL(Leave.Under11TOIL, 0) as float)) AS Under11TOIL, 
                               SUM(cast(ISNULL(Leave.Over12TOIL, 0) as float)) AS Over12TOIL , SUM(cast(ISNULL(Leave.Casual, 0) as float)) AS Casual,
							SUM(cast(ISNULL(Leave.LongService, 0) as float)) AS LongService,SUM(cast(ISNULL(Leave.Other, 0) as float)) AS Other
               FROM            Leave (nolock)
               INNER JOIN      StaffDetails (nolock) sd ON Leave.StaffNumber = sd.StaffNumber
				INNER JOIN ScheduledPeople (nolock) sp on sp.StaffDetailsID = sd.StaffID 
				INNER JOIN ScheduledPersonTeam_LINK (nolock) spl on spl.ScheduledPersonID = sp.ScheduledPersonID and  spl.IsHomeTeam = 1  and convert(datetime,EndDate,110) >= convert(datetime,convert(varchar,GETDATE(),110),110)
								  and (
								  convert(datetime, StartDate, 110) >= convert(datetime, convert(varchar,GETDATE(),110), 110) or
								  isnull(convert(datetime,EndDate,110),'9999-01-01') >= convert(datetime,convert(varchar(30),getdate(),110),110)
								  and convert(datetime,StartDate,110) <= convert(datetime,convert(varchar(30),getdate(),110),110))
			  
			   WHERE           (spl.TeamID = @intTeamID)  
               AND             (CONVERT(DATETIME, Leave.StartingDate, 102) >= CONVERT(DATETIME, @startdate, 102)) 
               AND             (CONVERT(DATETIME,  Leave.StartingDate, 102) <= CONVERT(DATETIME, @enddate, 102))
               AND             (Leave.Status <> 2)
               GROUP BY        Leave.StaffNumber
						
END