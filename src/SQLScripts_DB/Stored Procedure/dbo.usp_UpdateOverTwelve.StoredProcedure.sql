USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_UpdateOverTwelve]    Script Date: 02/11/2023 20:19:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE  [dbo].[usp_UpdateOverTwelve]
@AllocationID           INT,
@pNetLogin              VARCHAR(30),
@pMarkOverTwelve        SMALLINT,
@pOverTwelveHrs         INT,
@pIsOverseasOverTwelve  BIT

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
    DECLARE @vMarkOverTwelve        SMALLINT
    DECLARE @vOverTwelveHrs         INT
    DECLARE @vIsOverseasOverTwelve  BIT	
    DECLARE @vSQL                   VARCHAR(MAX)
	DECLARE @vHistory               NVARCHAR(MAX)	
	DECLARE @updateflag             INT = 0 
	DECLARE @vuserID                INT	
	DECLARE @vname          VARCHAR(100)
		
	BEGIN TRY
		
		SELECT  @vname =  CASE WHEN (sp.DisplayName IS NULL) 
	                         THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
                                       THEN (sd.Forename + ' ' + sd.Surname) 
									   ELSE (sd.PreferredForename + ' ' + sd.Surname)    END
                             ELSE sp.DisplayName END 
	    FROM StaffDetails sd (nolock)
		LEFT JOIN ScheduledPeople sp (nolock) on sp.StaffDetailsID = sd.StaffID
	   WHERE sd.NetLogin=@pNetLogin	

	   SELECT @vuserID = UserID 
		 FROM [dbo].[Users] 
		WHERE NetLogin = @pNetLogin  			
 
	  SELECT @vMarkOverTwelve       = MarkOverTwelve,
	         @vOverTwelveHrs        = OverTwelveHrs,
			 @vIsOverseasOverTwelve = IsOverseasOverTwelve
	    FROM Allocations 
	   WHERE ID = @AllocationID	   	  

	  SET @vSQL = 'UPDATE Allocations SET '
	  SET @vHistory = ''

	  IF ( ISNULL(@vMarkOverTwelve,0) <> ISNULL(@pMarkOverTwelve,0)  ) 
	   BEGIN 
		 SET @updateflag = 1	
		 SET @vSQL = @vSQL+' MarkOverTwelve = '+cast(@pMarkOverTwelve as varchar)+', '	
		 
		  IF ( ISNULL(@vMarkOverTwelve,0) IN (-1,0) AND  ISNULL(@pMarkOverTwelve,0) = 1  ) 
		   SET @vHistory = 'Marked For Over12 by '+@vname+' on ' 
		                   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'
		   
		  IF ( ISNULL(@vMarkOverTwelve,0) IN (-1,1) AND  ISNULL(@pMarkOverTwelve,0) = 0  ) 
		   SET @vHistory = 'UnMarked For Over12 by '+@vname+' on ' 
		                   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'	
		  
	   END	
	   
	  IF ( ISNULL(@vOverTwelveHrs,0) <> ISNULL(@pOverTwelveHrs,0)  ) 
	   BEGIN    
		 SET @vSQL = @vSQL+' OverTwelveHrs = '+cast(@pOverTwelveHrs as varchar)+', '	
		 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
		 SET @vHistory = @vHistory+'Over12 Hours Amount changed from '
						 + right('0' + CAST( isnull(@vOverTwelveHrs,0) / 3600 AS varchar(2)),2) + ':'  
						 + right('0' + CAST( (isnull(@vOverTwelveHrs,0) % 3600)/60 AS varchar(2)),2)+' To '
						 + right('0' + CAST( isnull(@pOverTwelveHrs,0) / 3600 AS varchar(2)),2) + ':'  
						 + right('0' + CAST( (isnull(@pOverTwelveHrs,0) % 3600)/60 AS varchar(2)),2)+' by '		 
  		                 +@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '+FORMAT(Getdate(),'HH:mm')+'.'
		 SET @updateflag = 1			 
	   END	  		

	  IF ( ISNULL(@vIsOverseasOverTwelve,0) <> ISNULL(@pIsOverseasOverTwelve,0)  ) 
	   BEGIN 	   
		 SET @vSQL = @vSQL+' IsOverseasOverTwelve = '+cast(@pIsOverseasOverTwelve as varchar)+', '	
		 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
		 SET @vHistory = @vHistory+'Overseas Deployment changed from '
		                 + case when @vIsOverseasOverTwelve = 0 THEN '[False]' ELSE '[True]' end + ' to '
		                 + case when @pIsOverseasOverTwelve = 0 THEN '[False]' ELSE '[True]' end + ' by '						 
		                 + @vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '+FORMAT(Getdate(),'HH:mm')+'.'
		 SET @updateflag = 1			 
	   END	  
	   
      IF ( @updateflag = 1)
	   BEGIN
	   
		 SET @vSQL = @vSQL+' UpdatedBy = '+ CAST(@vuserID AS VARCHAR)
						                  +', UpdatedDate = getutcdate()
					          WHERE ID = '+cast(@AllocationID as varchar)
		 EXEC (@vSQL)		 

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )		
			SELECT ht.id AS historytype,
				   @AllocationID AS attributeid,
				   'PH' AS HistorySubType,
				   getdate(),
				   @vuserID,
				   @vHistory
			  FROM HistoryTypes HT
			 WHERE historytype = 'AllocationDuty'	
			 
		UPDATE AL 
		   SET AL.MarkOverTwelve = TAL.MarkOverTwelve,
		       AL.OverTwelveHrs = TAL.OverTwelveHrs,
			   AL.IsOverseasOverTwelve = TAL.IsOverseasOverTwelve,
			   AL.UpdatedBy = @vuserID,
			   AL.UpdatedDate = getutcdate()
		  FROM Allocations AL
		 INNER JOIN Allocations TAL ON AL.SchedulingPersonID = TAL.SchedulingPersonID
		                           AND AL.WeekNumber = TAL.WeekNumber
								   AND AL.iDay = TAL.iDay
		 WHERE TAL.ID = @AllocationID
		   AND AL.ID <> @AllocationID
			 		 

	   END	   
	   
		SELECT 0 AS RETURNVAL
			
	END TRY
				
	BEGIN CATCH
			
		SELECT 1 AS RETURNVAL
			
	END CATCH;			
END	